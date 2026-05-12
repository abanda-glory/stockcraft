<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Str;

class InventoryGeneratorService
{
    private array $templates = [
        'supermarket' => [
            'categories' => ['Beverages', 'Dairy & Eggs', 'Bakery', 'Snacks & Confectionery', 'Canned Goods', 'Fresh Produce', 'Meat & Seafood', 'Frozen Foods', 'Household', 'Personal Care'],
            'products' => [
                'Beverages' => ['Coca-Cola 500ml', 'Pepsi 1.5L', 'Fanta Orange 330ml', 'Sprite Zero 500ml', 'Minute Maid Orange Juice 1L', 'Nestlé Pure Life Water 1.5L', 'Dano Full Cream Milk 500ml', 'Lipton Yellow Label Tea 100 bags', 'Nescafé Classic 200g', 'Milo 400g', '5-Alive Citrus Juice 1L', 'Malta Guinness 330ml'],
                'Dairy & Eggs' => ['Fresh White Eggs (crate 30)', 'Cowbell Milk 400g', 'Peak Full Cream Milk 400g', 'Lurpak Butter 200g', 'Kiri Cream Cheese 200g', 'Yaourt Activia Fraise 125g', 'Lactel Whole Milk 1L', 'Philadelphia Cream Cheese 300g'],
                'Bakery' => ['Sliced White Bread 600g', 'Whole Wheat Bread 500g', 'Croissant Butter x4', 'Baguette Tradition', 'Coconut Biscuits 200g', 'Digestive Biscuits 400g', 'Marie Biscuits 250g'],
                'Snacks & Confectionery' => ['Pringles Original 165g', 'Lay\'s Classic Chips 100g', 'Kit Kat 4-Finger 45g', 'Bounty Bar 57g', 'Snickers 50g', 'Twix Twin Bar 50g', 'Oreo Original 154g', 'Haribo Gold Bears 200g', 'Choco Milo 165g'],
                'Canned Goods' => ['Titus Sardines in Tomato 125g', 'Geisha Mackerel in Brine 155g', 'Heinz Baked Beans 415g', 'Hunts Tomato Paste 400g', 'Gino Tomato Plum 400g', 'Del Monte Sweetcorn 340g', 'Princes Tuna Chunks 185g'],
                'Fresh Produce' => ['Fresh Tomatoes 1kg', 'Ripe Plantains (bunch)', 'Onions 1kg', 'Fresh Pepper Assorted 500g', 'Carrots 1kg', 'Cabbage (head)', 'Cucumber each', 'Sweet Potatoes 1kg', 'Garlic Bulb each', 'Lemons x5'],
                'Meat & Seafood' => ['Chicken Thighs 1kg', 'Beef Mince 500g', 'Sausages 400g', 'Smoked Fish 500g', 'Prawns Frozen 500g', 'Chicken Drumsticks 1kg', 'Pork Chops 500g'],
                'Frozen Foods' => ['McCain French Fries 750g', 'Frozen Mixed Vegetables 500g', 'Birds Eye Fish Fingers 400g', 'Frozen Pizza Margherita 400g', 'Frozen Peas 500g'],
                'Household' => ['Omo Washing Powder 1kg', 'Ariel Detergent 500g', 'Sunlight Dish Soap 750ml', 'Vim Cream Cleaner 500ml', 'Mama Lemon Dishwash 750ml', 'Bbooster Insecticide 400ml'],
                'Personal Care' => ['Colgate Triple Action Toothpaste 75ml', 'Dove Soap Bar 100g', 'Lux Beauty Soap 80g', 'Nivea Body Lotion 250ml', 'Head & Shoulders Shampoo 200ml', 'Gillette Fusion Razor x2', 'Always Classic Pads x10'],
            ],
            'units' => ['pcs', 'kg', 'pack', 'bottle', 'box', 'crate', 'bunch'],
            'has_expiry' => true,
        ],
        'pharmacy' => [
            'categories' => ['Analgesics', 'Antibiotics', 'Vitamins & Supplements', 'Antimalaria', 'Cough & Cold', 'Gastrointestinal', 'Dermatology', 'Ophthalmology', 'Chronic Disease', 'Medical Devices'],
            'products' => [
                'Analgesics' => ['Paracetamol 500mg x16', 'Ibuprofen 400mg x10', 'Aspirin 300mg x20', 'Doliprane 1000mg x8', 'Efferalgan 500mg x16', 'Novalgin 500mg x10', 'Diclofenac 50mg x10', 'Tramadol 50mg x10'],
                'Antibiotics' => ['Amoxicillin 500mg x21', 'Augmentin 625mg x14', 'Ciprofloxacin 500mg x10', 'Azithromycin 500mg x3', 'Doxycycline 100mg x10', 'Metronidazole 500mg x10', 'Clarithromycin 500mg x14'],
                'Vitamins & Supplements' => ['Vitamin C 1000mg x30', 'Vitamin D3 1000IU x60', 'Zinc Sulfate 20mg x30', 'Iron Folate x30', 'Calcium Carbonate 600mg x30', 'Omega-3 Fish Oil x30', 'Multivitamin Adults x30', 'Folic Acid 5mg x28'],
                'Antimalaria' => ['Coartem 80/480mg x24', 'Artemether 80mg x6', 'Chloroquine 250mg x12', 'Quinine 500mg x12', 'Eurartesim 320/40mg x3', 'Pyramax 180/60mg x3'],
                'Cough & Cold' => ['Actifed Syrup 100ml', 'Piriton 4mg x20', 'Rhinathiol 5% Syrup 200ml', 'Mucosolvan 30mg x20', 'Pseudoephedrine 60mg x24', 'ORS Sachets x10', 'Strepsils Lemon x24'],
                'Gastrointestinal' => ['Omeprazole 20mg x14', 'Flagyl 500mg x14', 'Loperamide 2mg x12', 'Spasfon 80mg x30', 'Smecta Sachets x10', 'Duphalac 670mg/ml 200ml', 'Buscopan 10mg x20'],
                'Dermatology' => ['Betamethasone Cream 0.1% 30g', 'Clotrimazole 1% Cream 20g', 'Miconazole Cream 20g', 'Salicylic Acid 2% Lotion 100ml', 'Hydrocortisone 1% Cream 30g', 'Gentamicin Eye/Ear Drops 10ml'],
                'Ophthalmology' => ['Ciprofloxacin Eye Drops 5ml', 'Chloramphenicol Eye Ointment 4g', 'Visine Eye Drops 15ml', 'Dexamethasone Eye Drops 5ml'],
                'Chronic Disease' => ['Metformin 850mg x30', 'Amlodipine 5mg x30', 'Lisinopril 10mg x30', 'Atorvastatin 20mg x30', 'Glibenclamide 5mg x30', 'Furosemide 40mg x30', 'Levothyroxine 50mcg x30'],
                'Medical Devices' => ['Digital Thermometer', 'Blood Pressure Monitor', 'Blood Glucose Meter', 'Glucometer Test Strips x50', 'Disposable Gloves x100', 'Surgical Masks x50', 'Bandage Roll 5cm', 'Gauze Pad 10x10cm x10'],
            ],
            'units' => ['box', 'bottle', 'tube', 'pack', 'strip', 'pcs', 'sachet'],
            'has_expiry' => true,
        ],
        'electronics' => [
            'categories' => ['Smartphones', 'Laptops & PCs', 'Accessories', 'Audio & Headphones', 'Tablets', 'TV & Monitors', 'Networking', 'Cameras', 'Gaming', 'Power & Batteries'],
            'products' => [
                'Smartphones' => ['Samsung Galaxy A15 4G', 'Tecno Spark 20 Pro', 'Infinix Hot 40 Pro', 'iPhone 13 128GB', 'Xiaomi Redmi Note 13', 'Itel A70 64GB', 'Samsung Galaxy A05s', 'Tecno Camon 20', 'Nokia G22 64GB', 'Realme C55'],
                'Laptops & PCs' => ['HP 250 G9 Core i5', 'Dell Inspiron 15 Core i7', 'Lenovo IdeaPad 3 Core i3', 'Asus VivoBook 15 Ryzen 5', 'Acer Aspire 5 Core i5', 'HP EliteBook 840 G9', 'MacBook Air M2 13"'],
                'Accessories' => ['USB-C Fast Charging Cable 2m', 'OTG Flash Drive 64GB', 'Leather Phone Case Samsung A15', 'Screen Protector Tempered Glass', 'Wireless Mouse Logitech M185', 'USB Keyboard K120', 'HDMI Cable 3m', 'USB Hub 4-Port', 'Phone Ring Stand Holder', 'Car Charger Dual USB'],
                'Audio & Headphones' => ['JBL Go 3 Portable Speaker', 'Samsung Galaxy Buds2', 'Sony WH-1000XM5 Headphones', 'Jabra Evolve2 55 Headset', 'Anker Soundcore Motion+', 'Sennheiser HD 400S', 'AirPods 3rd Gen'],
                'Tablets' => ['Samsung Galaxy Tab A8 10.5"', 'iPad 10th Gen 64GB WiFi', 'Tecno MEGAPAD 10 4G', 'Lenovo Tab M10 Plus', 'Amazon Fire HD 10'],
                'TV & Monitors' => ['Samsung 43" 4K Smart TV', 'LG 32" Full HD Monitor', 'Hisense 55" QLED TV', 'Dell 24" IPS Monitor P2422H', 'TCL 50" Android TV', 'HP 27" FHD Monitor'],
                'Networking' => ['TP-Link AC750 WiFi Router', 'Cisco RV340 Dual WAN', 'D-Link DIR-842 AC1200', 'TP-Link TL-SF1008D Switch', 'CAT6 Ethernet Cable 10m', 'WiFi Range Extender RE305'],
                'Cameras' => ['Canon EOS 1500D DSLR', 'Logitech C920 Webcam', 'GoPro Hero 12 Black', 'Sony ZV-1 Vlog Camera', 'Security Camera Hikvision 2MP'],
                'Gaming' => ['PlayStation 5 Console', 'Xbox Series S', 'Nintendo Switch OLED', 'Gaming Mouse Logitech G203', 'Gaming Keyboard Redragon K552', 'Gaming Headset HyperX Cloud II'],
                'Power & Batteries' => ['Anker PowerCore 20000mAh', 'Romoss 30000mAh Power Bank', 'APC 650VA UPS', 'Duracell AA Batteries x8', 'Energizer AAA x4', 'Oraimo 65W GaN Charger'],
            ],
            'units' => ['pcs', 'unit', 'pack', 'set'],
            'has_expiry' => false,
        ],
        'restaurant' => [
            'categories' => ['Grains & Flour', 'Cooking Oils', 'Spices & Seasonings', 'Proteins', 'Vegetables', 'Beverages', 'Dairy', 'Packaging', 'Cleaning', 'Snacks & Drinks'],
            'products' => [
                'Grains & Flour' => ['Rice (50kg bag)', 'All-Purpose Flour 25kg', 'Semolina 5kg', 'Cornflour 5kg', 'Pasta Spaghetti 5kg', 'Oats 5kg', 'Cassava Flour 10kg', 'Plantain Flour 5kg'],
                'Cooking Oils' => ['Vegetable Cooking Oil 25L', 'Palm Oil 20L', 'Groundnut Oil 10L', 'Olive Oil 5L', 'Sunflower Oil 10L', 'Coconut Oil 5L'],
                'Spices & Seasonings' => ['Jumbo Seasoning Cubes 100x11g', 'Maggi Crevette Cubes x100', 'Black Pepper Powder 500g', 'Curry Powder 500g', 'Thyme Dried 200g', 'Bay Leaves 100g', 'Garlic Powder 500g', 'Cameroon Pepper 500g', 'Njangsa 500g', 'DJansang 500g', 'Salt 1kg', 'Crayfish Dried 500g'],
                'Proteins' => ['Chicken Wings 10kg', 'Beef Brisket 5kg', 'Pork Belly 5kg', 'Fresh Fish 5kg', 'Smoked Fish 2kg', 'Eggs Crate x30', 'Shrimps Frozen 2kg'],
                'Vegetables' => ['Tomatoes 5kg', 'Onions 10kg', 'Leeks 2kg', 'Celery 1kg', 'Carrots 5kg', 'Green Peppers 2kg', 'Spinach 2kg', 'Fresh Ginger 1kg', 'Fresh Garlic 1kg'],
                'Beverages' => ['Bottled Water 600ml x24', 'Coca-Cola Cans x24', 'Fanta 60cl x24', 'Heineken Beer 60cl x24', '33 Export Beer 60cl x24', 'Fruit Juice Assorted 1L x12'],
                'Dairy' => ['Fresh Milk 10L', 'Butter 2.5kg', 'Cream 1L', 'Cheese Emmental 500g'],
                'Packaging' => ['Takeaway Boxes (pack 50)', 'Plastic Cups x100', 'Wooden Forks x100', 'Food Bags Kraft x50', 'Napkins x200', 'Aluminum Foil Roll 100m'],
                'Cleaning' => ['Bleach Javel 5L', 'Multi-Surface Cleaner 5L', 'Dish Soap 5L', 'Gloves Latex L x100', 'Sponges x20', 'Hand Sanitizer 5L'],
                'Snacks & Drinks' => ['Peanuts Roasted 1kg', 'Beignets Mix 2kg', 'Soft Drinks Assorted x12'],
            ],
            'units' => ['kg', 'bag', 'crate', 'pack', 'bottle', 'L', 'pcs', 'box'],
            'has_expiry' => true,
        ],
        'fashion' => [
            'categories' => ['Men\'s Clothing', 'Women\'s Clothing', 'Children\'s Wear', 'Footwear', 'Accessories', 'Traditional Wear', 'Sportswear', 'Underwear & Socks'],
            'products' => [
                'Men\'s Clothing' => ['Men\'s Slim Fit Chinos Blue (32)', 'Men\'s Oxford Shirt White (M)', 'Men\'s Polo Shirt Navy (L)', 'Men\'s Jogger Pants Black (XL)', 'Men\'s Casual T-Shirt Grey (M)', 'Men\'s Blazer Dark Grey (L)', 'Men\'s Jeans Straight Cut (34)', 'Men\'s Traditional Embroidered Kaftan'],
                'Women\'s Clothing' => ['Women\'s Flare Dress Red (S)', 'Women\'s Blouse Floral Print (M)', 'Women\'s High-Waist Jeans (28)', 'Women\'s Pencil Skirt Black (M)', 'Women\'s Palazzo Trousers (L)', 'Women\'s Bodycon Dress (S)', 'Women\'s Wrap Dress African Print', 'Women\'s Peplum Top (M)'],
                'Children\'s Wear' => ['Kids T-Shirt Cotton Age 4-5', 'Girls Dress Age 6-7', 'Boys Shorts Age 8-9', 'School Uniform Set Age 10-11', 'Baby Onesie 0-3M', 'Kids Pajamas Age 3-4'],
                'Footwear' => ['Men\'s Sneakers White (42)', 'Women\'s Heels Black 7cm (38)', 'Men\'s Leather Shoes Brown (43)', 'Women\'s Flat Sandals (39)', 'Kids Sneakers (32)', 'Men\'s Slides (42)', 'Women\'s Boots Ankle (37)'],
                'Accessories' => ['Leather Belt Brown Men', 'Women\'s Handbag Black Leather', 'Sunglasses UV400 Wayfarer', 'Baseball Cap Unisex Navy', 'Scarf Silk Print', 'Men\'s Wallet Leather Black', 'Women\'s Earrings Gold Set', 'Wristwatch Casio Classic'],
                'Traditional Wear' => ['Kaba & Slit Set Women', 'Dashiki Men\'s African Print', 'Loincloth Ndop Pattern', 'Embroidered Grand Boubou', 'Ankara Wrapper 6 Yards', 'Atiku Fabric 5 Yards'],
                'Sportswear' => ['Running Shorts Men (M)', 'Sports Bra Women (S)', 'Gym Leggings Women (M)', 'Jersey Football (L)', 'Track Suit Set (XL)', 'Compression Socks'],
                'Underwear & Socks' => ['Men\'s Boxer Briefs 3-Pack (M)', 'Women\'s Brief Set 5-Pack (S)', 'Ankle Socks 6-Pack', 'Men\'s Crew Socks 3-Pack', 'Women\'s Seamless Bra (34B)'],
            ],
            'units' => ['pcs', 'set', 'pair', 'pack', 'yards'],
            'has_expiry' => false,
        ],
        'hardware' => [
            'categories' => ['Hand Tools', 'Power Tools', 'Electrical', 'Plumbing', 'Fasteners', 'Paint & Finishes', 'Safety Equipment', 'Building Materials', 'Welding', 'Garden & Outdoor'],
            'products' => [
                'Hand Tools' => ['Hammer Stanley 16oz', 'Screwdriver Set 12pcs Phillips/Flat', 'Adjustable Wrench 12"', 'Pliers Combination 8"', 'Tape Measure 5m Stanley', 'Level Spirit 60cm', 'Hacksaw Frame 12"', 'Chisel Set 6pcs Wood', 'Utility Knife Box Cutter', 'Allen Key Set Metric 9pcs'],
                'Power Tools' => ['Bosch Drill 13mm 650W', 'Makita Angle Grinder 115mm', 'Circular Saw 185mm 1200W', 'Jigsaw Variable Speed 650W', 'Orbital Sander 125mm', 'Impact Drill 550W Bosch', 'Cordless Screwdriver 12V'],
                'Electrical' => ['PVC Conduit 20mm x3m', 'Electrical Wire 2.5mm 100m roll', 'MCB Circuit Breaker 32A', 'DB Board 4-way', 'Wall Socket Double 13A', 'Extension Cable 10m 4-way', 'LED Bulb 18W E27', 'Fluorescent Tube 36W', 'Cable Ties 100pcs', 'Electrical Tape PVC'],
                'Plumbing' => ['PVC Pipe 3/4" x3m', 'PPR Pipe 20mm x4m', 'Ball Valve 1/2" Brass', 'PVC Elbow 90° 3/4"', 'Tap Mixer Basin Chrome', 'Shower Head Set 8"', 'Pipe Wrench 14"', 'Teflon Tape PTFE 12m', 'PVC Glue 250ml', 'Flexible Hose 40cm'],
                'Fasteners' => ['Wood Screws 4x40mm x200', 'Masonry Nails 3x70mm x1kg', 'Bolt & Nut M10x50mm x50', 'Rawl Plugs 8mm x100', 'Roofing Bolts M8x80mm x50', 'Cable Clamps 20mm x100', 'Anchor Bolt M12x75mm x25'],
                'Paint & Finishes' => ['Dulux Interior Emulsion 4L White', 'Crown Gloss Paint 4L', 'Sandpaper Assorted x10', 'Paint Brush Set 5pcs', 'Paint Roller 9" with Tray', 'Wood Varnish 1L Clear', 'Primer Coat 4L Grey', 'Putty Filler 5kg'],
                'Safety Equipment' => ['Hard Hat Yellow', 'Safety Boots Steel Toe (43)', 'Work Gloves Leather L', 'Safety Goggles Clear', 'Dust Mask N95 x10', 'Hi-Vis Jacket Yellow XL', 'Ear Muffs Protector', 'Safety Harness Full Body'],
                'Building Materials' => ['Cement 50kg bag', 'Sand Fine 1 Tonne', 'Iron Rod 12mm x12m', 'Plywood Sheet 18mm 4x8ft', 'Roofing Sheet 0.5mm x3m', 'Ceramic Floor Tile 60x60cm', 'POP Plaster 40kg', 'Block/Brick each'],
                'Welding' => ['Welding Electrodes 3.2mm x5kg', 'Welding Machine MMA 200A', 'Welding Helmet Auto-Darkening', 'Wire Brush Steel 6"', 'Angle Grinder Disc 115mm x10', 'Welding Gloves Leather'],
                'Garden & Outdoor' => ['Garden Hose 20m', 'Wheelbarrow 80L', 'Cutlass/Machete', 'Garden Fork 4-Prong', 'Rake Garden 14-Tine', 'Watering Can 10L', 'Hand Trowel Set', 'Lawn Mower Manual Push'],
            ],
            'units' => ['pcs', 'pack', 'box', 'roll', 'bag', 'set', 'each', 'meter'],
            'has_expiry' => false,
        ],
    ];

    public function generate(int $userId, array $options): array
    {
        $businessType = $options['business_type'];
        $count = (int)$options['count'];
        $minPrice = (float)$options['min_price'];
        $maxPrice = (float)$options['max_price'];
        $stockLevel = $options['stock_level'];
        $includeCategories = (bool)$options['include_categories'];
        $includeExpiry = (bool)$options['include_expiry'];

        $template = $this->templates[$businessType] ?? $this->templates['supermarket'];
        $generated = [];
        $categoryMap = [];

        // Create or get categories
        if ($includeCategories) {
            $colors = ['#6366f1', '#f59e0b', '#10b981', '#ef4444', '#8b5cf6', '#ec4899', '#14b8a6', '#f97316', '#06b6d4', '#84cc16'];
            foreach ($template['categories'] as $idx => $catName) {
                $cat = Category::firstOrCreate(
                    ['user_id' => $userId, 'name' => $catName],
                    ['color' => $colors[$idx % count($colors)]]
                );
                $categoryMap[$catName] = $cat->id;
            }
        }

        // Flatten all products with their categories
        $allProducts = [];
        foreach ($template['products'] as $cat => $items) {
            foreach ($items as $item) {
                $allProducts[] = ['name' => $item, 'category' => $cat];
            }
        }

        // Shuffle and pick
        shuffle($allProducts);
        $selectedProducts = array_slice($allProducts, 0, min($count, count($allProducts)));

        // If more requested than available, loop
        while (count($selectedProducts) < $count) {
            $extra = $allProducts;
            shuffle($extra);
            foreach ($extra as $item) {
                if (count($selectedProducts) >= $count) break;
                // Slightly modify name to avoid duplicate SKU issues
                $item['name'] = $item['name'] . ' (Variant ' . rand(2, 9) . ')';
                $selectedProducts[] = $item;
            }
        }

        [$minQty, $maxQty] = $this->getStockRange($stockLevel);

        foreach ($selectedProducts as $productData) {
            $buyingPrice = round($minPrice + mt_rand() / mt_getrandmax() * ($maxPrice - $minPrice), 0);
            $markup = $this->getMarkup($businessType);
            $sellingPrice = round($buyingPrice * $markup, 0);
            $quantity = rand($minQty, $maxQty);
            $reorderLevel = (int)round($maxQty * 0.15);

            $expiry = null;
            if ($includeExpiry && $template['has_expiry']) {
                // Not all items need expiry - perishables mainly
                if (rand(0, 100) < 65) {
                    $expiry = Carbon::now()->addDays(rand(30, 730))->format('Y-m-d');
                }
            }

            $unit = $template['units'][array_rand($template['units'])];

            $product = Product::create([
                'user_id'       => $userId,
                'category_id'   => $includeCategories ? ($categoryMap[$productData['category']] ?? null) : null,
                'name'          => $productData['name'],
                'sku'           => $this->generateSKU($businessType, $productData['name']),
                'description'   => $this->generateDescription($productData['name'], $businessType),
                'buying_price'  => $buyingPrice,
                'selling_price' => $sellingPrice,
                'quantity'      => $quantity,
                'reorder_level' => $reorderLevel,
                'unit'          => $unit,
                'expiry_date'   => $expiry,
                'business_type' => $businessType,
                'is_active'     => true,
            ]);

            $generated[] = $product;
        }

        return $generated;
    }

    private function generateSKU(string $businessType, string $productName): string
    {
        $prefix = strtoupper(substr($businessType, 0, 2));
        $words = explode(' ', $productName);
        $namePart = '';
        foreach (array_slice($words, 0, 2) as $word) {
            $namePart .= strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $word), 0, 3));
        }
        $suffix = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

        $sku = $prefix . '-' . substr($namePart, 0, 5) . '-' . $suffix;

        // Ensure uniqueness
        $original = $sku;
        $i = 1;
        while (Product::where('sku', $sku)->exists()) {
            $sku = $original . '-' . $i;
            $i++;
        }

        return $sku;
    }

    private function generateDescription(string $productName, string $businessType): string
    {
        $descriptions = [
            'supermarket' => "Quality {name} available in stock. Fresh and ready for purchase. Sourced from trusted suppliers.",
            'pharmacy'    => "{name} — Pharmaceutical grade. Please consult a healthcare professional before use. Store as directed.",
            'electronics' => "{name} — High-performance electronics product. Warranty included. Contact us for technical support.",
            'restaurant'  => "{name} — Premium quality ingredient for professional kitchen use. Bulk supply available.",
            'fashion'     => "{name} — Trendy and comfortable fashion item. Available in multiple sizes. Machine washable.",
            'hardware'    => "{name} — Professional grade tool/material. Suitable for construction and maintenance work.",
        ];

        $template = $descriptions[$businessType] ?? $descriptions['supermarket'];
        return str_replace('{name}', $productName, $template);
    }

    private function getMarkup(string $businessType): float
    {
        $markups = [
            'supermarket' => [1.15, 1.35],
            'pharmacy'    => [1.20, 1.45],
            'electronics' => [1.10, 1.30],
            'restaurant'  => [1.25, 1.60],
            'fashion'     => [1.40, 2.20],
            'hardware'    => [1.20, 1.50],
        ];

        $range = $markups[$businessType] ?? [1.20, 1.40];
        return $range[0] + mt_rand() / mt_getrandmax() * ($range[1] - $range[0]);
    }

    private function getStockRange(string $level): array
    {
        return match ($level) {
            'low'    => [1, 25],
            'medium' => [25, 150],
            'high'   => [100, 500],
            default  => [10, 100],
        };
    }

    public function getBusinessTypes(): array
    {
        return array_keys($this->templates);
    }
}
