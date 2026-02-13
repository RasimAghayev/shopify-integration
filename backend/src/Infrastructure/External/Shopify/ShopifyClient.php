<?php

declare(strict_types=1);

namespace Src\Infrastructure\External\Shopify;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Src\Application\Contracts\LoggerInterface;
use Src\Application\Contracts\ShopifyClientInterface;

final class ShopifyClient implements ShopifyClientInterface
{
    private const string MCP_ENDPOINT = 'https://discover.shopifyapps.com/global/mcp';

    private string $adminApiBaseUrl;

    private string $oauthEndpoint;

    public function __construct(
        private readonly string $shopDomain,
        private readonly string $accessToken,
        private readonly LoggerInterface $logger,
        private readonly string $apiVersion = '2026-01',
        private readonly string $clientId = '',
        private readonly string $clientSecret = '',
        private readonly string $savedCatalog = '',
    ) {
        $this->adminApiBaseUrl = "https://{$this->shopDomain}/admin/api/{$this->apiVersion}";
        $this->oauthEndpoint = "https://{$this->shopDomain}/admin/oauth/access_token";
    }

    /**
     * @return array<string, mixed>
     * @throws \Illuminate\Http\Client\ConnectionException
     * @throws \JsonException
     */
    public function getProduct(string $shopifyId): array
    {
        if ($this->canUseAdminApi()) {
            $result = $this->adminApiRequest('GET', "products/{$shopifyId}.json");

            if (! isset($result['product'])) {
                throw new ShopifyApiException(
                    "Product not found with ID: {$shopifyId}",
                    404,
                );
            }

            return $result['product'];
        }

        // Fallback to MCP API (for global catalog browsing)
        $result = $this->mcpRequest('tools/call', [
            'name' => 'search_global_products',
            'query' => $shopifyId,
            'context' => '',
            'limit' => 1,
            'saved_catalog' => $this->savedCatalog,
        ]);

        $this->logger->debug('getProduct raw result', [
            'shopify_id' => $shopifyId,
            'result' => $result,
        ]);

        $products = $this->extractProductsFromMcpResponse($result);

        $this->logger->debug('getProduct extracted products', [
            'count' => count($products),
            'products' => $products,
        ]);

        if (empty($products)) {
            throw new ShopifyApiException(
                "Product not found with ID: {$shopifyId}",
                404,
            );
        }

        return $products[0];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getProducts(int $page = 1, int $limit = 50): array
    {
        if ($this->canUseAdminApi()) {
            $result = $this->adminApiRequest('GET', 'products.json', [
                'limit' => min($limit, 250), // Shopify max is 250
            ]);

            return $result['products'] ?? [];
        }

        // Fallback to MCP API
        $result = $this->mcpRequest('tools/call', [
            'name' => 'search_global_products',
            'query' => '',
            'context' => '',
            'limit' => $limit,
            'saved_catalog' => $this->savedCatalog,
        ]);

        return $this->extractProductsFromMcpResponse($result);
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     * @throws \Illuminate\Http\Client\ConnectionException|\JsonException
     */
    public function updateProduct(string $shopifyId, array $data): array
    {
        if ($this->canUseAdminApi()) {
            $result = $this->adminApiRequest('PUT', "products/{$shopifyId}.json", [
                'product' => $data,
            ]);

            return $result['product'] ?? [];
        }

        // MCP API is read-only for global catalog, return the current product
        $this->logger->warning('updateProduct not supported via MCP API', [
            'shopify_id' => $shopifyId,
        ]);

        return $this->getProduct($shopifyId);
    }

    /**
     * @throws \Illuminate\Http\Client\ConnectionException
     */
    public function getProductsCount(): int
    {
        if ($this->canUseAdminApi()) {
            $result = $this->adminApiRequest('GET', 'products/count.json');

            return $result['count'] ?? 0;
        }

        $products = $this->getProducts(1, 250);

        return count($products);
    }

    /**
     * @return array<string, mixed>
     * @throws \Illuminate\Http\Client\ConnectionException
     */
    public function updateInventory(string $inventoryItemId, int $quantity): array
    {
        if ($this->canUseAdminApi()) {
            // First get the inventory level to find location_id
            $inventoryLevels = $this->adminApiRequest('GET', 'inventory_levels.json', [
                'inventory_item_ids' => $inventoryItemId,
            ]);

            if (empty($inventoryLevels['inventory_levels'])) {
                $this->logger->warning('No inventory levels found', [
                    'inventory_item_id' => $inventoryItemId,
                ]);

                return [];
            }

            $locationId = $inventoryLevels['inventory_levels'][0]['location_id'];

            $result = $this->adminApiRequest('POST', 'inventory_levels/set.json', [
                'location_id' => $locationId,
                'inventory_item_id' => $inventoryItemId,
                'available' => $quantity,
            ]);

            return $result['inventory_level'] ?? [];
        }

        // MCP API doesn't support inventory updates
        $this->logger->warning('updateInventory not supported via MCP API', [
            'inventory_item_id' => $inventoryItemId,
        ]);

        return [];
    }

    /**
     * Check if we have a static access token (shpat_*)
     */
    private function hasStaticToken(): bool
    {
        return ! empty($this->accessToken)
            && $this->accessToken !== 'your-access-token'
            && str_starts_with($this->accessToken, 'shpat_');
    }

    /**
     * Check if we can use OAuth client credentials
     */
    private function canUseOAuth(): bool
    {
        return ! empty($this->clientId)
            && ! empty($this->clientSecret)
            && $this->clientId !== 'your-api-key'
            && $this->clientSecret !== 'your-api-secret';
    }

    /**
     * Get access token - either static or via OAuth
     * @throws \Illuminate\Http\Client\ConnectionException
     */
    private function getAccessToken(): string
    {
        // If we have a static token, use it
        if ($this->hasStaticToken()) {
            return $this->accessToken;
        }

        // Otherwise try OAuth client credentials flow
        if ($this->canUseOAuth()) {
            return $this->getOAuthToken();
        }

        throw new ShopifyApiException(
            'No valid Shopify credentials configured. Provide either SHOPIFY_ACCESS_TOKEN (shpat_*) or SHOPIFY_API_KEY + SHOPIFY_API_SECRET.',
            401,
        );
    }

    /**
     * @throws \Illuminate\Http\Client\ConnectionException
     */
    private function getOAuthToken(): string
    {
        $cacheKey = "shopify_oauth_token_{$this->shopDomain}";

        if ($token = Cache::get($cacheKey)) {
            $this->logger->debug('Using cached OAuth token');

            return $token;
        }

        $this->logger->debug('Requesting new OAuth token', [
            'endpoint' => $this->oauthEndpoint,
            'client_id' => $this->clientId,
        ]);

        // Use form-urlencoded as per Shopify docs
        $response = Http::asForm()->post($this->oauthEndpoint, [
            'grant_type' => 'client_credentials',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
        ]);

        if ($response->failed()) {
            $body = $response->body();

            // Check for common OAuth errors
            if (str_contains($body, 'app_not_installed')) {
                $this->logger->error('OAuth failed: App not installed on store', [
                    'shop_domain' => $this->shopDomain,
                ]);

                throw new ShopifyApiException(
                    'Shopify app is not installed on this store. Please use Admin API access token (shpat_*) from a Custom App instead. '.
                    'Go to Shopify Admin → Settings → Apps → Develop apps → Create app → Install → Get Admin API access token.',
                    401,
                );
            }

            $this->logger->error('OAuth token request failed', [
                'status' => $response->status(),
                'body' => $body,
            ]);

            throw new ShopifyApiException(
                'Failed to obtain OAuth token: '.$body,
                $response->status(),
            );
        }

        $data = $response->json();
        $token = $data['access_token'];
        $expiresIn = $data['expires_in'] ?? 3600; // Default 1 hour

        // Cache for expires_in - 60 seconds (safety margin)
        $cacheMinutes = max(1, (int) (($expiresIn - 60) / 60));
        Cache::put($cacheKey, $token, now()->addMinutes($cacheMinutes));

        $this->logger->debug('OAuth token obtained and cached', [
            'expires_in' => $expiresIn,
            'cache_minutes' => $cacheMinutes,
        ]);

        return $token;
    }

    /**
     * @throws \Illuminate\Http\Client\ConnectionException
     * @throws \JsonException
     */
    private function mcpRequest(string $method, array $arguments = []): array
    {
        $token = $this->getOAuthToken();

        $this->logger->debug('Shopify MCP request', [
            'method' => $method,
            'arguments' => $arguments,
        ]);

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => "Bearer {$token}",
        ])->post(self::MCP_ENDPOINT, [
            'jsonrpc' => '2.0',
            'method' => $method,
            'id' => 1,
            'params' => [
                'name' => $arguments['name'] ?? $method,
                'arguments' => $arguments,
            ],
        ]);

        if ($response->failed()) {
            $this->logger->error('Shopify MCP error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new ShopifyApiException(
                "Shopify MCP API returned error: {$response->status()}",
                $response->status(),
            );
        }

        $result = $response->json();

        $this->logger->debug('Shopify MCP response', [
            'result' => $result,
        ]);

        if (isset($result['error'])) {
            throw new ShopifyApiException(
                'Shopify MCP error: '. json_encode($result['error'], JSON_THROW_ON_ERROR),
                400,
            );
        }

        return $result['result'] ?? $result;
    }

    /**
     * Make Admin REST API request
     *
     * @return array<string, mixed>
     * @throws \Illuminate\Http\Client\ConnectionException
     */
    private function adminApiRequest(string $method, string $endpoint, array $data = []): array
    {
        $url = "{$this->adminApiBaseUrl}/{$endpoint}";
        $token = $this->getAccessToken();

        $this->logger->debug('Shopify Admin API request', [
            'method' => $method,
            'url' => $url,
            'data' => $data,
        ]);

        $request = Http::withHeaders([
            'Content-Type' => 'application/json',
            'X-Shopify-Access-Token' => $token,
        ]);

        $response = match (strtoupper($method)) {
            'GET' => $request->get($url, $data),
            'POST' => $request->post($url, $data),
            'PUT' => $request->put($url, $data),
            'DELETE' => $request->delete($url),
            default => throw new ShopifyApiException("Unsupported HTTP method: {$method}", 400),
        };

        if ($response->failed()) {
            $this->logger->error('Shopify Admin API error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new ShopifyApiException(
                "Shopify Admin API returned error: {$response->status()} - {$response->body()}",
                $response->status(),
            );
        }

        $result = $response->json();

        $this->logger->debug('Shopify Admin API response', [
            'result' => $result,
        ]);

        return $result;
    }

    /**
     * Check if Admin API is available (via static token or OAuth)
     */
    private function canUseAdminApi(): bool
    {
        return $this->hasStaticToken() || $this->canUseOAuth();
    }

    /**
     * @param  array<string, mixed>  $result
     * @return array<int, array<string, mixed>>
     */
    private function extractProductsFromMcpResponse(array $result): array
    {
        $this->logger->debug('Extracting products from MCP response', [
            'result_type' => gettype($result),
            'is_list' => array_is_list($result),
        ]);

        $products = [];

        // MCP returns array of products directly or in 'content' wrapper
        $items = $result;
        if (isset($result['content']) && is_array($result['content'])) {
            $items = $result['content'];
        }

        // Check if it's a direct array of products
        if (! empty($items) && isset($items[0]['id'])) {
            foreach ($items as $item) {
                $product = $this->transformMcpProduct($item);
                if ($product) {
                    $products[] = $product;
                }
            }
        }

        $this->logger->debug('Extracted products', ['count' => count($products)]);

        return $products;
    }

    /**
     * Transform MCP product format to standard Shopify Admin API format
     *
     * @param  array<string, mixed>  $mcpProduct
     * @return array<string, mixed>|null
     */
    private function transformMcpProduct(array $mcpProduct): ?array
    {
        if (empty($mcpProduct['variants'])) {
            $this->logger->warning('MCP product has no variants', ['product_id' => $mcpProduct['id'] ?? 'unknown']);

            return null;
        }

        $firstVariant = $mcpProduct['variants'][0];

        // Extract numeric product ID from productId like "gid://shopify/Product/15348106133888"
        $shopifyProductId = null;
        if (isset($firstVariant['productId']) && preg_match('/Product\/(\d+)/', $firstVariant['productId'], $matches)) {
            $shopifyProductId = $matches[1];
        }

        // Price is in cents, convert to dollars
        $price = '0.00';
        if (isset($firstVariant['price']['amount'])) {
            $price = number_format($firstVariant['price']['amount'] / 100, 2, '.', '');
        }

        // Build variants array in standard format
        $variants = [];
        foreach ($mcpProduct['variants'] as $index => $variant) {
            $variantPrice = '0.00';
            if (isset($variant['price']['amount'])) {
                $variantPrice = number_format($variant['price']['amount'] / 100, 2, '.', '');
            }

            // Extract variant ID number
            $variantId = null;
            if (isset($variant['id']) && preg_match('/ProductVariant\/(\d+)/', $variant['id'], $matches)) {
                $variantId = $matches[1];
            }

            $variants[] = [
                'id' => $variantId ?? ($index + 1),
                'price' => $variantPrice,
                'sku' => $variant['sku'] ?? 'MCP-'.($shopifyProductId ?? 'unknown').'-'.($index + 1),
                'inventory_quantity' => $variant['inventory_quantity'] ?? 0,
                'title' => $variant['displayName'] ?? $mcpProduct['title'] ?? 'Default',
            ];
        }

        // Generate handle from title
        $handle = '';
        if (! empty($mcpProduct['title'])) {
            $handle = strtolower(preg_replace('/[^a-z0-9]+/', '-', strtolower($mcpProduct['title'])));
            $handle = trim($handle, '-');
        }

        return [
            'id' => $shopifyProductId,
            'title' => $mcpProduct['title'] ?? 'Untitled Product',
            'body_html' => $mcpProduct['description'] ?? '',
            'handle' => $handle,
            'status' => 'active',
            'variants' => $variants,
        ];
    }
}
