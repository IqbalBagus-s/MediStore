<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Product;

class CategoryProductSeeder extends Seeder
{
    public function run()
    {
        $categoriesData = [
            [
                'category_name' => 'Alat Pemeriksaan Dasar',
                'products' => [
                    [
                        'name' => 'Termometer Digital',
                        'description' => 'Termometer digital akurat untuk mengukur suhu tubuh.',
                        'image' => null,
                        'price' => 45000,
                        'stock' => 25,
                    ],
                    [
                        'name' => 'Tensimeter Manual',
                        'description' => 'Alat ukur tekanan darah dengan pompa manual dan stetoskop.',
                        'image' => null,
                        'price' => 175000,
                        'stock' => 15,
                    ],
                    [
                        'name' => 'Oximeter',
                        'description' => 'Alat untuk mengukur kadar oksigen dalam darah dan detak jantung.',
                        'image' => null,
                        'price' => 120000,
                        'stock' => 30,
                    ],
                ],
            ],
            [
                'category_name' => 'Alat Bantu Jalan',
                'products' => [
                    [
                        'name' => 'Tongkat Jalan',
                        'description' => 'Tongkat alumunium ringan untuk membantu berjalan.',
                        'image' => null,
                        'price' => 85000,
                        'stock' => 20,
                    ],
                    [
                        'name' => 'Kursi Roda Lipat',
                        'description' => 'Kursi roda praktis dengan desain lipat.',
                        'image' => null,
                        'price' => 1200000,
                        'stock' => 10,
                    ],
                    [
                        'name' => 'Walker Lipat',
                        'description' => 'Alat bantu jalan dengan 4 kaki yang dapat dilipat.',
                        'image' => null,
                        'price' => 450000,
                        'stock' => 12,
                    ],
                ],
            ],
            [
                'category_name' => 'Peralatan Medis Lainnya',
                'products' => [
                    [
                        'name' => 'Stetoskop',
                        'description' => 'Stetoskop untuk memeriksa detak jantung dan pernapasan.',
                        'image' => null,
                        'price' => 110000,
                        'stock' => 18,
                    ],
                    [
                        'name' => 'Nebulizer Portable',
                        'description' => 'Alat untuk terapi pernapasan dengan uap obat.',
                        'image' => null,
                        'price' => 375000,
                        'stock' => 8,
                    ],
                    [
                        'name' => 'Kotak P3K',
                        'description' => 'Kotak pertolongan pertama lengkap isi 20 item.',
                        'image' => null,
                        'price' => 95000,
                        'stock' => 40,
                    ],
                ],
            ],
        ];

        foreach ($categoriesData as $categoryData) {
            $category = Category::create([
                'category_name' => $categoryData['category_name'],
            ]);

            foreach ($categoryData['products'] as $productData) {
                Product::create([
                    'category_id' => $category->category_id,
                    'name' => $productData['name'],
                    'description' => $productData['description'],
                    'image' => $productData['image'],
                    'price' => $productData['price'],
                    'stock' => $productData['stock'],
                ]);
            }

            echo "✅ Kategori '{$category->category_name}' berhasil dibuat dengan " . count($categoryData['products']) . " produk\n";
        }

        echo "\n🎉 Seeder berhasil dijalankan! Total " . count($categoriesData) . " kategori telah dibuat.\n";
    }
}
