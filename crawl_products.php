<?php

require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\DomCrawler\Crawler;

$http = HttpClient::create([
    'timeout' => 40,
    'headers' => [
        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
    ]
]);

$baseUrl = 'https://xxivstore.com';
$brandsUrl = $baseUrl . '/thuong-hieu/';

echo " Đang lấy danh sách thương hiệu...\n";
$response = $http->request('GET', $brandsUrl);
$html = $response->getContent();
$crawler = new Crawler($html);

$brands = $crawler->filter('.item-letter li')->each(function (Crawler $node) use ($baseUrl) {
    $linkNode = $node->filter('a');
    $imgNode  = $node->filter('img');
    if (!$linkNode->count()) return null;

    $href = $linkNode->attr('href');
    $logo = $imgNode->count() ? $imgNode->attr('src') : null;

    return [
        'name' => trim($linkNode->text()),
        'url'  => str_starts_with($href, 'http') ? $href : $baseUrl . $href,
        'logo' => $logo ? (str_starts_with($logo, 'http') ? $logo : $baseUrl . $logo) : null,
    ];
});

$brands = array_filter($brands);
echo " Tìm thấy " . count($brands) . " thương hiệu.\n\n";

$results = [];

foreach ($brands as $brand) {
    echo " Đang crawl thương hiệu: {$brand['name']}...\n";
    $brandData = [
        'name' => $brand['name'],
        'logo' => $brand['logo'],
        'products' => [],
    ];

    try {
        $response = $http->request('GET', $brand['url']);
        $brandHtml = $response->getContent();
        $brandCrawler = new Crawler($brandHtml);

        $products = $brandCrawler->filter('ul.products li.product')->each(function (Crawler $node) use ($baseUrl, $http) {
            $name = $node->filter('.woocommerce-loop-product__title')->count() ? trim($node->filter('.woocommerce-loop-product__title')->text()) : null;
            $brandName = $node->filter('.brand')->count() ? trim($node->filter('.brand')->text()) : null;
            $price = $node->filter('.price')->count() ? trim($node->filter('.price')->text()) : null;
            $img = $node->filter('img')->count() ? $node->filter('img')->attr('src') : null;
            $img = str_starts_with($img, 'http') ? $img : $baseUrl . $img;

            $classAttr = $node->attr('class');
            $gender = 'Khác';
            if (strpos($classAttr, 'men-attr') !== false) $gender = 'Nam';
            elseif (strpos($classAttr, 'women-attr') !== false) $gender = 'Nữ';
            elseif (strpos($classAttr, 'unisex-attr') !== false) $gender = 'Unisex';

            $link = $node->filter('a.woocommerce-LoopProduct-link')->count()
                ? $node->filter('a.woocommerce-LoopProduct-link')->attr('href')
                : null;

            $description = null;

            if ($link) {
                try {
                    $res = $http->request('GET', $link);
                    $detailHtml = $res->getContent();
                    $detailCrawler = new Crawler($detailHtml);

                    $selectors = [
                        '.el-content.uk-panel.uk-margin-top',
                        '.woocommerce-Tabs-panel--description',
                        '.woocommerce-product-details__short-description',
                        '.product-short-description',
                        '.woocommerce-product-details__description',
                    ];

                    foreach ($selectors as $selector) {
                        if ($detailCrawler->filter($selector)->count()) {
                            $description = trim($detailCrawler->filter($selector)->text());
                            if ($description !== '') break;
                        }
                    }
                } catch (\Exception $e) {
                    echo " Lỗi lấy mô tả: {$e->getMessage()}\n";
                }
            }

            return [
                'brand' => $brandName,
                'name' => $name,
                'price' => $price,
                'image' => $img,
                'gender' => $gender,
                'description' => $description,
            ];
        });

        $brandData['products'] = $products;
        $results[] = $brandData;

        echo " Đã lấy " . count($products) . " sản phẩm.\n";
    } catch (\Exception $e) {
        echo " Lỗi thương hiệu {$brand['name']}: {$e->getMessage()}\n";
    }

    sleep(1);
}

$filePath = __DIR__ . '/storage/app/products.json';
file_put_contents($filePath, json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

echo "\n Hoàn tất! Đã lưu dữ liệu tại: $filePath\n";
