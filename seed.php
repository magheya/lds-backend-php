<?php
// seed.php
require_once 'api/database.php';
require_once 'api/data-access.php';
require_once 'api/init.php';

// Initialize database
initSchema();

function seedDatabase() {
    echo "Seeding database...\n";
    
    $dataAccess = new DataAccess();
    
    // Sample products
    $sampleProducts = [
        [
            'name' => 'T-shirt solidaire',
            'price' => 20,
            'description' => 'T-shirt 100% coton bio avec notre logo.',
            'image' => '../assets/clothes/tshirt1.jpg',
            'category' => 'clothing',
            'stock' => 100,
            'sizes' => ['S', 'M', 'L', 'XL'],
        ],
        [
            'name' => 'T-shirt événement',
            'price' => 25,
            'description' => 'T-shirt édition limitée pour notre événement annuel.',
            'image' => '../assets/clothes/tshirt2.jpg',
            'category' => 'clothing',
            'stock' => 75,
            'sizes' => ['S', 'M', 'L', 'XL'],
        ],
        [
            'name' => 'Sweat à capuche',
            'price' => 40,
            'description' => 'Sweat à capuche confortable avec notre logo brodé.',
            'image' => '../assets/clothes/sweat1.jpg',
            'category' => 'clothing',
            'stock' => 50,
            'sizes' => ['S', 'M', 'L', 'XL'],
        ],
        [
            'name' => 'Pull solidaire',
            'price' => 35,
            'description' => 'Pull chaud parfait pour l\'hiver.',
            'image' => '../assets/clothes/sweat2.jpg',
            'category' => 'clothing',
            'stock' => 60,
            'sizes' => ['S', 'M', 'L', 'XL'],
        ],
        [
            'name' => 'Tote bag',
            'price' => 15,
            'description' => 'Sac en toile réutilisable.',
            'image' => '../assets/clothes/bag.jpg',
            'category' => 'accessories',
            'stock' => 200,
            'sizes' => [],
        ],
        [
            'name' => 'Casquette',
            'price' => 18,
            'description' => 'Casquette brodée avec notre logo.',
            'image' => '../assets/clothes/cap.jpg',
            'category' => 'accessories',
            'stock' => 120,
            'sizes' => ['Unique'],
        ],
    ];

    // Sample events
    $sampleEvents = [
        [
            'name' => 'Tournoi de sport',
            'date' => '2025-06-24',
            'description' => 'Rejoignez-nous pour notre grand tournoi sportif annuel ! Au programme : football, basketball et bien d\'autres activités. Une journée conviviale ouverte à tous les niveaux.',
            'image' => '../assets/Evenements/Ev sport.jpg',
            'type' => 'upcoming',
        ],
        [
            'name' => 'Tournoi de ping-pong',
            'date' => '2025-07-15',
            'description' => 'Un tournoi de ping-pong ouvert à tous, débutants comme confirmés. Venez défier d\'autres joueurs dans une ambiance détendue et amicale.',
            'image' => '../assets/Evenements/Ev pingpong.jpg',
            'type' => 'upcoming',
        ],
        [
            'name' => 'Maraude solidaire',
            'date' => '2025-08-10',
            'description' => 'Participez à notre maraude mensuelle. Nous distribuerons des repas chauds et des kits d\'hygiène aux personnes sans-abri de la ville. Votre aide sera précieuse !',
            'image' => '../assets/Logo/Maraude.png',
            'type' => 'solidarity',
        ],
        [
            'name' => 'Collecte de vêtements',
            'date' => '2025-09-02',
            'description' => 'Grande collecte de vêtements pour les plus démunis. Nous avons besoin de bénévoles pour trier les dons et préparer les colis à distribuer.',
            'image' => '../assets/Logo/Don de vetements.png',
            'type' => 'solidarity',
        ],
        [
            'name' => 'Tournoi de poker caritatif',
            'date' => '2024-03-12',
            'description' => 'Notre tournoi de poker caritatif a permis de récolter 2500€ pour financer nos actions solidaires. Merci à tous les participants !',
            'image' => '../assets/Evenements/Ev poker.jpg',
            'type' => 'past',
        ],
        [
            'name' => 'Marathon jeux vidéo',
            'date' => '2024-02-05',
            'description' => '24h de jeux vidéo non-stop dans une ambiance conviviale. L\'événement a réuni plus de 100 participants et nous avons récolté 1800€ pour notre association.',
            'image' => '../assets/Evenements/Ev jeux videos.jpg',
            'type' => 'past',
        ],
    ];

    // Sample registrations
    $sampleRegistrations = [
        [
            'event_id' => 1,
            'name' => 'Jean Dupont',
            'email' => 'jean.dupont@example.com',
            'phone' => '0612345678',
        ],
        [
            'event_id' => 1,
            'name' => 'Marie Lambert',
            'email' => 'marie.lambert@example.com',
            'phone' => '0687654321',
        ],
        [
            'event_id' => 2,
            'name' => 'Thomas Bernard',
            'email' => 'thomas.bernard@example.com',
            'phone' => '0678901234',
        ],
        [
            'event_id' => 3,
            'name' => 'Sophie Martin',
            'email' => 'sophie.martin@example.com',
            'phone' => '0645678901',
        ],
    ];

    // Sample donations
    $sampleDonations = [
        [
            'type' => 'money',
            'amount' => 50.0,
            'name' => 'Pierre Robert',
            'email' => 'pierre.robert@example.com',
            'description' => 'Don mensuel',
        ],
        [
            'type' => 'money',
            'amount' => 100.0,
            'name' => 'Isabelle Léger',
            'email' => 'isabelle.leger@example.com',
            'description' => 'Don pour le projet d\'hiver',
        ],
        [
            'type' => 'clothes',
            'amount' => null,
            'name' => 'Michel Blanchard',
            'email' => 'michel.blanchard@example.com',
            'description' => 'Vêtements d\'hiver pour adultes',
        ],
        [
            'type' => 'food',
            'amount' => null,
            'name' => 'Julie Moreau',
            'email' => 'julie.moreau@example.com',
            'description' => 'Conserves et produits non périssables',
        ],
    ];

    // Sample messages
    $sampleMessages = [
        [
            'name' => 'François Dubois',
            'email' => 'francois.dubois@example.com',
            'subject' => 'Question sur le bénévolat',
            'message' => 'Bonjour, je souhaiterais avoir plus d\'informations sur comment devenir bénévole pour votre association. Quelles sont les démarches à suivre ? Merci d\'avance pour votre réponse.',
        ],
        [
            'name' => 'Cécile Petit',
            'email' => 'cecile.petit@example.com',
            'subject' => 'Demande de partenariat',
            'message' => 'Bonjour, je représente une entreprise locale et nous aimerions établir un partenariat avec votre association pour soutenir vos actions. Pouvons-nous organiser une rencontre pour en discuter ?',
        ],
        [
            'name' => 'Alexandre Leroy',
            'email' => 'alexandre.leroy@example.com',
            'subject' => 'Problème avec l\'inscription',
            'message' => 'Bonjour, j\'ai essayé de m\'inscrire à l\'événement du 15 juillet mais je rencontre des difficultés. Le formulaire ne semble pas fonctionner correctement. Pouvez-vous m\'aider ?',
        ],
    ];

    try {
        // Check if we already have products
        $existingProducts = $dataAccess->getAllProducts();
        
        if (empty($existingProducts)) {
            // Add sample products
            foreach ($sampleProducts as $product) {
                $dataAccess->addProduct($product);
            }
            echo "Added sample products\n";
        } else {
            echo "Products already exist, skipping products seed\n";
        }
        
        // Check if we already have events
        $existingEvents = $dataAccess->getAllEvents();
        
        if (empty($existingEvents)) {
            // Add sample events
            foreach ($sampleEvents as $event) {
                $dataAccess->addEvent($event);
            }
            echo "Added sample events\n";
        } else {
            echo "Events already exist, skipping events seed\n";
        }
        
        // Check if we already have registrations
        $existingRegistrations = $dataAccess->getAllRegistrations();
        
        if (empty($existingRegistrations)) {
            // Add sample registrations
            foreach ($sampleRegistrations as $registration) {
                $dataAccess->saveRegistration($registration);
            }
            echo "Added sample registrations\n";
        } else {
            echo "Registrations already exist, skipping registrations seed\n";
        }
        
        // Check if we already have donations
        $existingDonations = $dataAccess->getAllDonations();
        
        if (empty($existingDonations)) {
            // Add sample donations
            foreach ($sampleDonations as $donation) {
                $dataAccess->saveDonation($donation);
            }
            echo "Added sample donations\n";
        } else {
            echo "Donations already exist, skipping donations seed\n";
        }
        
        // Check if we already have messages
        $existingMessages = $dataAccess->getAllMessages();
        
        if (empty($existingMessages)) {
            // Add sample messages
            foreach ($sampleMessages as $message) {
                $dataAccess->saveMessage($message);
            }
            echo "Added sample messages\n";
        } else {
            echo "Messages already exist, skipping messages seed\n";
        }
        
        // Add orders with sample data
        $existingOrders = $dataAccess->getAllOrders();
        
        if (empty($existingOrders)) {
            // Get actual product IDs
            $allProducts = $dataAccess->getAllProducts();
            
            if (!empty($allProducts)) {
                // Find product IDs by name
                $tshirtId = null;
                $sweatId = null;
                $toteId = null;
                $capId = null;
                
                foreach ($allProducts as $p) {
                    if ($p['name'] === 'T-shirt solidaire') $tshirtId = $p['id'];
                    if ($p['name'] === 'Sweat à capuche') $sweatId = $p['id'];
                    if ($p['name'] === 'Tote bag') $toteId = $p['id'];
                    if ($p['name'] === 'Casquette') $capId = $p['id'];
                }
                
                // Use default IDs if not found
                if (!$tshirtId) $tshirtId = 1;
                if (!$sweatId) $sweatId = 3;
                if (!$toteId) $toteId = 5;
                if (!$capId) $capId = 6;
                
                $sampleOrders = [
                    [
                        'customer_name' => 'Laurent Girard',
                        'customer_email' => 'laurent.girard@example.com',
                        'total' => 40.0,
                        'status' => 'delivered',
                        'items' => [
                            [
                                'id' => $tshirtId,
                                'size' => 'M',
                                'quantity' => 2,
                                'price' => 20.0,
                            ],
                        ],
                    ],
                    [
                        'customer_name' => 'Catherine Bonnet',
                        'customer_email' => 'catherine.bonnet@example.com',
                        'total' => 65.0,
                        'status' => 'processing',
                        'items' => [
                            [
                                'id' => $sweatId,
                                'size' => 'L',
                                'quantity' => 1,
                                'price' => 40.0,
                            ],
                            [
                                'id' => $toteId,
                                'size' => null,
                                'quantity' => 1,
                                'price' => 15.0,
                            ],
                            [
                                'id' => $capId,
                                'size' => 'Unique',
                                'quantity' => 1,
                                'price' => 10.0,
                            ],
                        ],
                    ],
                ];
                
                foreach ($sampleOrders as $order) {
                    try {
                        $dataAccess->saveOrder($order);
                        echo "Added order for {$order['customer_name']}\n";
                    } catch (Exception $e) {
                        echo "Error adding order for {$order['customer_name']}: {$e->getMessage()}\n";
                    }
                }
            } else {
                echo "No products found, skipping order seeding\n";
            }
        } else {
            echo "Orders already exist, skipping orders seed\n";
        }
        
        echo "Database seeded successfully\n";
    } catch (Exception $e) {
        echo "Error seeding database: " . $e->getMessage() . "\n";
    }
}

// Run the seeding process
seedDatabase();