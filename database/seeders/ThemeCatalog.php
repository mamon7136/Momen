<?php

namespace Database\Seeders;

/** Built-in themes: each one has its own templates in resources/views/themes/{slug} and CSS in public/themes/{slug}. */
class ThemeCatalog
{
    public static function all(): array
    {
        return [
            'nile' => ['name' => 'النيل', 'price' => 0, 'settings' => [
                'meta' => ['category' => 'general', 'description' => 'ثيم متكامل ونظيف يناسب كل أنواع المتاجر، بطاقات منتجات مع زر إضافة سريع.', 'tags' => ['متعدد الأغراض', 'سريع', 'مجاني']],
                'colors' => ['primary' => '#0f766e', 'secondary' => '#f59e0b', 'background' => '#ffffff', 'text' => '#13201f'],
                'font' => 'Tajawal',
                'sections' => [
                    ['type' => 'hero', 'title' => 'كل ما تحتاجه في مكان واحد', 'subtitle' => 'منتجات مختارة بعناية وتوصيل سريع لباب بيتك'],
                    ['type' => 'features_bar'],
                    ['type' => 'categories', 'title' => 'تسوّق حسب التصنيف'],
                    ['type' => 'featured_products', 'title' => 'منتجات مميزة'],
                    ['type' => 'latest_products', 'title' => 'وصل حديثاً'],
                    ['type' => 'whatsapp_cta'],
                ],
            ]],
            'zahra' => ['name' => 'زهرة', 'price' => 0, 'settings' => [
                'meta' => ['category' => 'fashion', 'description' => 'تصميم أنيق بأسلوب المجلات لمتاجر الأزياء والعبايات والإكسسوارات، صور كبيرة وخطوط هادئة.', 'tags' => ['أزياء', 'عبايات', 'فاخر']],
                'colors' => ['primary' => '#9a5b3c', 'secondary' => '#d9b99b', 'background' => '#faf6f1', 'text' => '#2b2118'],
                'font' => 'Almarai',
                'sections' => [
                    ['type' => 'hero', 'title' => 'جمالك يبدأ من هنا', 'subtitle' => 'تشكيلة جديدة من الجلابيات والثياب السودانية الأصيلة'],
                    ['type' => 'categories'],
                    ['type' => 'featured_products', 'title' => 'اختياراتنا لكِ'],
                    ['type' => 'text', 'title' => 'صُنعت بحب في السودان', 'subtitle' => 'كل قطعة في متجرنا مختارة بعناية من أفضل الخياطين والمصممين المحليين.'],
                    ['type' => 'latest_products', 'title' => 'وصل حديثاً'],
                ],
            ]],
            'souq' => ['name' => 'سوق', 'price' => 0, 'settings' => [
                'meta' => ['category' => 'electronics', 'description' => 'واجهة سوق إلكتروني مزدحمة بالعروض للإلكترونيات والمتاجر الكبيرة: بحث بارز، أقسام، وشارات خصم.', 'tags' => ['إلكترونيات', 'سوبرماركت', 'عروض']],
                'colors' => ['primary' => '#1d4ed8', 'secondary' => '#ffb020', 'background' => '#ffffff', 'text' => '#111827'],
                'font' => 'Cairo',
                'sections' => [
                    ['type' => 'hero', 'title' => 'أحدث الأجهزة بأفضل الأسعار', 'subtitle' => 'سماعات، ساعات وجوالات أصلية بضمان'],
                    ['type' => 'categories'],
                    ['type' => 'featured_products', 'title' => '🔥 عروض اليوم'],
                    ['type' => 'features_bar'],
                    ['type' => 'latest_products', 'title' => 'الأكثر طلباً'],
                ],
            ]],
            'oud' => ['name' => 'عود', 'price' => 0, 'settings' => [
                'meta' => ['category' => 'perfume', 'description' => 'ثيم داكن فاخر بلمسات ذهبية لمتاجر العطور والبخور والدلكة والهدايا الراقية.', 'tags' => ['عطور', 'بخور', 'فاخر']],
                'colors' => ['primary' => '#c8a45a', 'secondary' => '#c8a45a', 'background' => '#121012', 'text' => '#f2e9dc'],
                'font' => 'Amiri',
                'sections' => [
                    ['type' => 'hero', 'title' => 'فخامة تُروى', 'subtitle' => 'بخور وعطور ودلكة سودانية أصيلة بروائح لا تُنسى'],
                    ['type' => 'featured_products', 'title' => 'المجموعة المختارة'],
                    ['type' => 'text', 'title' => 'حكاية العود', 'subtitle' => 'من تقاليد البيت السوداني نصنع لك روائح تحمل الدفء والكرم.'],
                    ['type' => 'categories'],
                    ['type' => 'latest_products', 'title' => 'جديدنا'],
                ],
            ]],
        ];
    }

    public static function categories(): array
    {
        return ['general' => 'متعدد الأغراض', 'fashion' => 'أزياء', 'electronics' => 'إلكترونيات', 'perfume' => 'عطور وهدايا'];
    }
}
