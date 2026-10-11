<?php
/**
 * CraveHub - Database Seeder
 * Populates MongoDB / Document Store with categories, mouthwatering food items, admin user, test user, and promo coupons.
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';

$db = getDB();

echo "Starting CraveHub Database Seeding...\n";

// 1. Seed Categories
$categories = [
    [
        '_id' => 'cat_pizza',
        'name' => 'Pizzas',
        'slug' => 'pizzas',
        'icon' => 'fa-pizza-slice',
        'image' => 'https://images.unsplash.com/photo-1513104890138-7c749659a591?w=600&auto=format&fit=crop&q=80',
        'desc' => 'Hand-tossed crusts with gooey cheese and gourmet toppings'
    ],
    [
        '_id' => 'cat_burger',
        'name' => 'Burgers & Wraps',
        'slug' => 'burgers',
        'icon' => 'fa-burger',
        'image' => 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=600&auto=format&fit=crop&q=80',
        'desc' => 'Loaded juicy patties, melted cheddar and crisp lettuce'
    ],
    [
        '_id' => 'cat_biryani',
        'name' => 'Biryani & Rice',
        'slug' => 'biryani',
        'icon' => 'fa-bowl-rice',
        'image' => 'https://images.unsplash.com/photo-1563379091339-03b21ab4a4f8?w=600&auto=format&fit=crop&q=80',
        'desc' => 'Authentic dum biryani infused with fragrant spices and saffron'
    ],
    [
        '_id' => 'cat_north_indian',
        'name' => 'North Indian',
        'slug' => 'north-indian',
        'icon' => 'fa-utensils',
        'image' => 'https://images.unsplash.com/photo-1585937421612-70a008356fbe?w=600&auto=format&fit=crop&q=80',
        'desc' => 'Creamy paneer butter masala, dal makhani, and garlic naans'
    ],
    [
        '_id' => 'cat_chinese',
        'name' => 'Asian & Momos',
        'slug' => 'asian',
        'icon' => 'fa-bowl-food',
        'image' => 'https://images.unsplash.com/photo-1541696432-82c6da8ce7bf?w=600&auto=format&fit=crop&q=80',
        'desc' => 'Steamy momos, wok-tossed hakka noodles and crispy chilli paneer'
    ],
    [
        '_id' => 'cat_desserts',
        'name' => 'Desserts & Shakes',
        'slug' => 'desserts',
        'icon' => 'fa-ice-cream',
        'image' => 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?w=600&auto=format&fit=crop&q=80',
        'desc' => 'Decadent chocolate lava cakes, thick shakes, and ice creams'
    ],
    [
        '_id' => 'cat_beverages',
        'name' => 'Coolers & Drinks',
        'slug' => 'beverages',
        'icon' => 'fa-glass-water',
        'image' => 'https://images.unsplash.com/photo-1513558161293-cdaf765ed2fd?w=600&auto=format&fit=crop&q=80',
        'desc' => 'Refreshing iced mojitos, cold brew coffees, and fruit juices'
    ]
];

foreach ($categories as $cat) {
    $existing = $db->findOne('categories', ['slug' => $cat['slug']]);
    if (!$existing) {
        $db->insert('categories', $cat);
    }
}
echo "✓ Categories seeded.\n";

// 2. Seed Foods
$foods = [
    // PIZZAS
    [
        'title' => 'Truffle Margherita Pizza',
        'category' => 'Pizzas',
        'category_slug' => 'pizzas',
        'price' => 349,
        'original_price' => 449,
        'is_veg' => true,
        'spicy_level' => 'Mild',
        'prep_time' => '20-25 mins',
        'rating' => 4.9,
        'rating_count' => 380,
        'is_bestseller' => true,
        'is_available' => true,
        'image' => 'https://images.unsplash.com/photo-1604382355076-af4b0eb60143?w=700&auto=format&fit=crop&q=80',
        'description' => 'Classic sourdough crust topped with San Marzano tomato sauce, fresh buffalo mozzarella, aromatic basil leaves, and a drizzle of white truffle oil.'
    ],
    [
        'title' => 'Smoky BBQ Chicken Pizza',
        'category' => 'Pizzas',
        'category_slug' => 'pizzas',
        'price' => 449,
        'original_price' => 549,
        'is_veg' => false,
        'spicy_level' => 'Medium',
        'prep_time' => '25 mins',
        'rating' => 4.8,
        'rating_count' => 290,
        'is_bestseller' => true,
        'is_available' => true,
        'image' => 'https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?w=700&auto=format&fit=crop&q=80',
        'description' => 'Char-grilled succulent chicken cubes smothered in hickory BBQ sauce, red onions, smoked gouda, and fresh cilantro on crisp crust.'
    ],
    [
        'title' => 'Fiery Peri-Peri Paneer Pizza',
        'category' => 'Pizzas',
        'category_slug' => 'pizzas',
        'price' => 379,
        'original_price' => 459,
        'is_veg' => true,
        'spicy_level' => 'Hot',
        'prep_time' => '20 mins',
        'rating' => 4.7,
        'rating_count' => 210,
        'is_bestseller' => false,
        'is_available' => true,
        'image' => 'https://images.unsplash.com/photo-1593560708920-61dd98c46a4e?w=700&auto=format&fit=crop&q=80',
        'description' => 'Spiced cottage cheese, crunchy bell peppers, jalapeños, and sweet corn topped with spicy peri-peri herb drizzle and mozzarella.'
    ],

    // BURGERS
    [
        'title' => 'Double Cheddar Smash Burger',
        'category' => 'Burgers & Wraps',
        'category_slug' => 'burgers',
        'price' => 259,
        'original_price' => 320,
        'is_veg' => false,
        'spicy_level' => 'Mild',
        'prep_time' => '15-20 mins',
        'rating' => 4.9,
        'rating_count' => 510,
        'is_bestseller' => true,
        'is_available' => true,
        'image' => 'https://images.unsplash.com/photo-1586190848861-99aa4a171e90?w=700&auto=format&fit=crop&q=80',
        'description' => 'Two juicy smash patties layered with molten aged English cheddar, caramelized onions, crisp pickles, and our signature secret Crave sauce on a toasted brioche bun.'
    ],
    [
        'title' => 'Crispy Zesty Paneer Burger',
        'category' => 'Burgers & Wraps',
        'category_slug' => 'burgers',
        'price' => 219,
        'original_price' => 269,
        'is_veg' => true,
        'spicy_level' => 'Medium',
        'prep_time' => '15 mins',
        'rating' => 4.8,
        'rating_count' => 340,
        'is_bestseller' => true,
        'is_available' => true,
        'image' => 'https://images.unsplash.com/photo-1550547660-d9450f859349?w=700&auto=format&fit=crop&q=80',
        'description' => 'Crunchy herb-crumbed paneer slab, creamy thousand island dressing, fresh iceberg lettuce, and juicy tomatoes inside soft buttered sesame buns.'
    ],
    [
        'title' => 'Falafel Hummus Wheat Wrap',
        'category' => 'Burgers & Wraps',
        'category_slug' => 'burgers',
        'price' => 199,
        'original_price' => 249,
        'is_veg' => true,
        'spicy_level' => 'Mild',
        'prep_time' => '12 mins',
        'rating' => 4.6,
        'rating_count' => 180,
        'is_bestseller' => false,
        'is_available' => true,
        'image' => 'https://images.unsplash.com/photo-1626700051175-6818013e1d4f?w=700&auto=format&fit=crop&q=80',
        'description' => 'Golden fried chickpea falafels with velvety garlic hummus, pickled cucumbers, diced tomatoes, and tahini drizzle rolled in toasted multigrain tortilla.'
    ],

    // BIRYANI
    [
        'title' => 'Royal Dum Hyderabadi Mutton Biryani',
        'category' => 'Biryani & Rice',
        'category_slug' => 'biryani',
        'price' => 429,
        'original_price' => 499,
        'is_veg' => false,
        'spicy_level' => 'Hot',
        'prep_time' => '25-30 mins',
        'rating' => 4.9,
        'rating_count' => 640,
        'is_bestseller' => true,
        'is_available' => true,
        'image' => 'https://images.unsplash.com/photo-1589302168068-964664d93dc0?w=700&auto=format&fit=crop&q=80',
        'description' => 'Slow-cooked tender cuts of marinated mutton layered with long-grain aged basmati rice, caramelized onions, mint, and pure saffron. Served with spicy salan and creamy raita.'
    ],
    [
        'title' => 'Shahi Awadhi Veg Biryani',
        'category' => 'Biryani & Rice',
        'category_slug' => 'biryani',
        'price' => 289,
        'original_price' => 349,
        'is_veg' => true,
        'spicy_level' => 'Medium',
        'prep_time' => '20-25 mins',
        'rating' => 4.7,
        'rating_count' => 420,
        'is_bestseller' => false,
        'is_available' => true,
        'image' => 'https://images.unsplash.com/photo-1642821373181-696a54913e9a?w=700&auto=format&fit=crop&q=80',
        'description' => 'Garden-fresh florets, carrots, french beans, and tender paneer simmered in aromatic spices and ghee, sealed in an earthen clay pot for deep aroma.'
    ],
    [
        'title' => 'Boneless Chicken Tikka Biryani',
        'category' => 'Biryani & Rice',
        'category_slug' => 'biryani',
        'price' => 349,
        'original_price' => 419,
        'is_veg' => false,
        'spicy_level' => 'Medium',
        'prep_time' => '20 mins',
        'rating' => 4.8,
        'rating_count' => 490,
        'is_bestseller' => true,
        'is_available' => true,
        'image' => 'https://images.unsplash.com/photo-1563379091339-03b21ab4a4f8?w=700&auto=format&fit=crop&q=80',
        'description' => 'Charcoal-grilled juicy boneless chicken tikka chunks mingled with spiced biryani rice, roasted cashews, and fried onions.'
    ],

    // NORTH INDIAN
    [
        'title' => 'Butter Chicken Deluxe',
        'category' => 'North Indian',
        'category_slug' => 'north-indian',
        'price' => 389,
        'original_price' => 460,
        'is_veg' => false,
        'spicy_level' => 'Mild',
        'prep_time' => '20 mins',
        'rating' => 4.9,
        'rating_count' => 780,
        'is_bestseller' => true,
        'is_available' => true,
        'image' => 'https://images.unsplash.com/photo-1603894584373-5ac82b2ae398?w=700&auto=format&fit=crop&q=80',
        'description' => 'Classic Delhi-style shredded tandoori chicken cooked in velvety, sweet-tangy makhani gravy enriched with fresh butter, cream, and kasuri methi.'
    ],
    [
        'title' => 'Paneer Tikka Butter Masala',
        'category' => 'North Indian',
        'category_slug' => 'north-indian',
        'price' => 319,
        'original_price' => 380,
        'is_veg' => true,
        'spicy_level' => 'Medium',
        'prep_time' => '20 mins',
        'rating' => 4.8,
        'rating_count' => 510,
        'is_bestseller' => true,
        'is_available' => true,
        'image' => 'https://images.unsplash.com/photo-1631452180519-c014fe946bc7?w=700&auto=format&fit=crop&q=80',
        'description' => 'Clay-oven charred cottage cheese cubes folded into a rich, luscious cashew-tomato gravy garnished with ginger juliennes and fresh cream.'
    ],
    [
        'title' => 'Dal Makhani Bukhara',
        'category' => 'North Indian',
        'category_slug' => 'north-indian',
        'price' => 269,
        'original_price' => 320,
        'is_veg' => true,
        'spicy_level' => 'Mild',
        'prep_time' => '15 mins',
        'rating' => 4.9,
        'rating_count' => 610,
        'is_bestseller' => false,
        'is_available' => true,
        'image' => 'https://images.unsplash.com/photo-1546833999-b9f581a1996d?w=700&auto=format&fit=crop&q=80',
        'description' => 'Slow-cooked whole black lentils and kidney beans simmered overnight on charcoal with butter and cream for that iconic velvety smokiness.'
    ],
    [
        'title' => 'Garlic Butter Naan (2 Pcs)',
        'category' => 'North Indian',
        'category_slug' => 'north-indian',
        'price' => 99,
        'original_price' => 120,
        'is_veg' => true,
        'spicy_level' => 'Mild',
        'prep_time' => '10 mins',
        'rating' => 4.9,
        'rating_count' => 820,
        'is_bestseller' => true,
        'is_available' => true,
        'image' => 'https://images.unsplash.com/photo-1601050690597-df0568f70950?w=700&auto=format&fit=crop&q=80',
        'description' => 'Soft, blistered tandoori flatbreads generously brushed with roasted garlic and salted Amul butter.'
    ],

    // ASIAN & MOMOS
    [
        'title' => 'Steamed Darjeeling Chicken Momos',
        'category' => 'Asian & Momos',
        'category_slug' => 'asian',
        'price' => 189,
        'original_price' => 229,
        'is_veg' => false,
        'spicy_level' => 'Hot',
        'prep_time' => '15 mins',
        'rating' => 4.9,
        'rating_count' => 450,
        'is_bestseller' => true,
        'is_available' => true,
        'image' => 'https://images.unsplash.com/photo-1625220194771-7ebdea0b7109?w=700&auto=format&fit=crop&q=80',
        'description' => 'Delicate, translucent dumplings packed with finely minced spiced chicken, spring onions, and ginger. Served with fiery red chili garlic dip.'
    ],
    [
        'title' => 'Crispy Schezwan Veg Hakka Noodles',
        'category' => 'Asian & Momos',
        'category_slug' => 'asian',
        'price' => 229,
        'original_price' => 279,
        'is_veg' => true,
        'spicy_level' => 'Hot',
        'prep_time' => '15 mins',
        'rating' => 4.7,
        'rating_count' => 310,
        'is_bestseller' => false,
        'is_available' => true,
        'image' => 'https://images.unsplash.com/photo-1585032226651-759b368d7246?w=700&auto=format&fit=crop&q=80',
        'description' => 'Wok-tossed noodles with shredded cabbage, bell peppers, carrots, and spring onions in our bold in-house Schezwan sauce.'
    ],
    [
        'title' => 'Chilli Paneer Dry Starters',
        'category' => 'Asian & Momos',
        'category_slug' => 'asian',
        'price' => 249,
        'original_price' => 299,
        'is_veg' => true,
        'spicy_level' => 'Hot',
        'prep_time' => '15 mins',
        'rating' => 4.8,
        'rating_count' => 280,
        'is_bestseller' => false,
        'is_available' => true,
        'image' => 'https://images.unsplash.com/photo-1541696432-82c6da8ce7bf?w=700&auto=format&fit=crop&q=80',
        'description' => 'Crisp-fried paneer cubes tossed with diced capsicum, onions, dark soy sauce, green chillies, and crushed black pepper.'
    ],

    // DESSERTS & SHAKES
    [
        'title' => 'Molten Belgian Choco Lava Cake',
        'category' => 'Desserts & Shakes',
        'category_slug' => 'desserts',
        'price' => 149,
        'original_price' => 199,
        'is_veg' => true,
        'spicy_level' => 'Sweet',
        'prep_time' => '10 mins',
        'rating' => 4.9,
        'rating_count' => 930,
        'is_bestseller' => true,
        'is_available' => true,
        'image' => 'https://images.unsplash.com/photo-1606313564200-e75d5e30476c?w=700&auto=format&fit=crop&q=80',
        'description' => 'Warm, fluffy chocolate sponge cake oozing with hot, liquid dark chocolate center. A heaven for chocoholics!'
    ],
    [
        'title' => 'Nutella Hazelnut Thick Shake',
        'category' => 'Desserts & Shakes',
        'category_slug' => 'desserts',
        'price' => 189,
        'original_price' => 239,
        'is_veg' => true,
        'spicy_level' => 'Sweet',
        'prep_time' => '10 mins',
        'rating' => 4.8,
        'rating_count' => 380,
        'is_bestseller' => true,
        'is_available' => true,
        'image' => 'https://images.unsplash.com/photo-1572490122747-3968b75cc699?w=700&auto=format&fit=crop&q=80',
        'description' => 'Thick creamy milkshake blended with original Nutella, vanilla bean ice cream, topped with chocolate drizzle and crushed roasted hazelnuts.'
    ],
    [
        'title' => 'Gulab Jamun with Rabri (2 Pcs)',
        'category' => 'Desserts & Shakes',
        'category_slug' => 'desserts',
        'price' => 129,
        'original_price' => 160,
        'is_veg' => true,
        'spicy_level' => 'Sweet',
        'prep_time' => '5 mins',
        'rating' => 4.9,
        'rating_count' => 540,
        'is_bestseller' => false,
        'is_available' => true,
        'image' => 'https://images.unsplash.com/photo-1601050690597-df0568f70950?w=700&auto=format&fit=crop&q=80',
        'description' => 'Melt-in-mouth golden khoya dumplings soaked in rose cardamom sugar syrup, served with chilled malai rabri and pistachio slivers.'
    ],

    // BEVERAGES
    [
        'title' => 'Virgin Mint Lime Mojito',
        'category' => 'Coolers & Drinks',
        'category_slug' => 'beverages',
        'price' => 119,
        'original_price' => 149,
        'is_veg' => true,
        'spicy_level' => 'Refreshing',
        'prep_time' => '5 mins',
        'rating' => 4.8,
        'rating_count' => 270,
        'is_bestseller' => true,
        'is_available' => true,
        'image' => 'https://images.unsplash.com/photo-1513558161293-cdaf765ed2fd?w=700&auto=format&fit=crop&q=80',
        'description' => 'Crushed fresh garden mint leaves, zesty lemon wedges, cane sugar, and sparkling soda served on the rocks.'
    ],
    [
        'title' => 'Caramel Cold Brew Frappe',
        'category' => 'Coolers & Drinks',
        'category_slug' => 'beverages',
        'price' => 169,
        'original_price' => 210,
        'is_veg' => true,
        'spicy_level' => 'Sweet',
        'prep_time' => '5 mins',
        'rating' => 4.7,
        'rating_count' => 310,
        'is_bestseller' => false,
        'is_available' => true,
        'image' => 'https://images.unsplash.com/photo-1517701604599-bb29b565090c?w=700&auto=format&fit=crop&q=80',
        'description' => 'Slow-extracted arabica cold brew blended with creamy milk, vanilla syrup, and ribboned with salted caramel drizzle.'
    ]
];

foreach ($foods as $food) {
    $existing = $db->findOne('foods', ['title' => $food['title']]);
    if (!$existing) {
        $db->insert('foods', $food);
    }
}
echo "✓ " . count($foods) . " food items seeded.\n";

// 3. Seed Admin and Test User
$adminUser = [
    '_id' => 'admin_001',
    'name' => 'CraveHub Admin',
    'email' => 'admin@cravehub.com',
    'phone' => '9998887770',
    'password' => password_hash('admin123', PASSWORD_BCRYPT),
    'role' => 'admin',
    'status' => 'active',
    'created_at' => date('Y-m-d H:i:s')
];
if (!$db->findOne('users', ['email' => $adminUser['email']])) {
    $db->insert('users', $adminUser);
    echo "✓ Admin user seeded (admin@cravehub.com / admin123).\n";
}

$sampleUser = [
    '_id' => 'user_001',
    'name' => 'Rahul Sharma',
    'email' => 'rahul@gmail.com',
    'phone' => '9876543210',
    'address' => 'Flat 402, Sunshine Heights, 100ft Road, Indiranagar, Bengaluru, 560038',
    'password' => password_hash('user123', PASSWORD_BCRYPT),
    'role' => 'user',
    'status' => 'active',
    'created_at' => date('Y-m-d H:i:s')
];
if (!$db->findOne('users', ['email' => $sampleUser['email']])) {
    $db->insert('users', $sampleUser);
    echo "✓ Sample user seeded (rahul@gmail.com / user123).\n";
}

// 4. Seed Coupons
global $PROMO_CODES;
foreach ($PROMO_CODES as $code => $details) {
    $couponDoc = array_merge(['_id' => 'coupon_' . strtolower($code), 'code' => $code], $details);
    if (!$db->findOne('coupons', ['code' => $code])) {
        $db->insert('coupons', $couponDoc);
    }
}
echo "✓ Promo coupons seeded.\n";

// 5. Seed a Sample Past Order for Rahul Sharma
$sampleOrder = [
    '_id' => 'ORD-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8)),
    'user_id' => 'user_001',
    'customer_name' => 'Rahul Sharma',
    'customer_email' => 'rahul@gmail.com',
    'customer_phone' => '9876543210',
    'delivery_address' => 'Flat 402, Sunshine Heights, 100ft Road, Indiranagar, Bengaluru, 560038',
    'delivery_notes' => 'Please ring the doorbell and leave at door',
    'items' => [
        [
            'food_id' => 'seed_1',
            'title' => 'Truffle Margherita Pizza',
            'price' => 349,
            'qty' => 1,
            'image' => 'https://images.unsplash.com/photo-1604382355076-af4b0eb60143?w=700&auto=format&fit=crop&q=80'
        ],
        [
            'food_id' => 'seed_2',
            'title' => 'Virgin Mint Lime Mojito',
            'price' => 119,
            'qty' => 2,
            'image' => 'https://images.unsplash.com/photo-1513558161293-cdaf765ed2fd?w=700&auto=format&fit=crop&q=80'
        ]
    ],
    'subtotal' => 587,
    'discount' => 120,
    'coupon_code' => 'TASTY50',
    'delivery_fee' => 0,
    'tax' => 23.35,
    'total_amount' => 490.35,
    'payment_method' => 'upi',
    'payment_status' => 'paid',
    'transaction_id' => 'TXN' . time() . 'UPI',
    'status' => ORDER_STATUS_OUT_FOR_DELIVERY,
    'delivery_partner' => [
        'name' => 'Rajesh Kumar',
        'phone' => '+91 98231 44556',
        'vehicle' => 'Honda Activa (KA-03-EX-4491)',
        'rating' => 4.9,
        'photo' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=200&auto=format&fit=crop&q=80'
    ],
    'status_history' => [
        ['status' => ORDER_STATUS_PLACED, 'time' => date('Y-m-d H:i:s', strtotime('-25 minutes')), 'note' => 'Order placed successfully'],
        ['status' => ORDER_STATUS_CONFIRMED, 'time' => date('Y-m-d H:i:s', strtotime('-20 minutes')), 'note' => 'Kitchen confirmed order'],
        ['status' => ORDER_STATUS_PREPARING, 'time' => date('Y-m-d H:i:s', strtotime('-12 minutes')), 'note' => 'Chef is preparing your delicious meal'],
        ['status' => ORDER_STATUS_OUT_FOR_DELIVERY, 'time' => date('Y-m-d H:i:s', strtotime('-4 minutes')), 'note' => 'Partner Rajesh Kumar is on the way']
    ],
    'created_at' => date('Y-m-d H:i:s', strtotime('-25 minutes')),
    'updated_at' => date('Y-m-d H:i:s')
];

if ($db->count('orders') === 0) {
    $db->insert('orders', $sampleOrder);
    echo "✓ Sample live order seeded (ID: " . $sampleOrder['_id'] . ").\n";
}

echo "Database seeding finished successfully!\n";
