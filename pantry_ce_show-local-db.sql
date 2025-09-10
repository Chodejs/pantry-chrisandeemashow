-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Sep 10, 2025 at 12:31 PM
-- Server version: 8.0.42
-- PHP Version: 8.2.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pantry_ce_show`
--

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` int NOT NULL,
  `recipe_id` int NOT NULL,
  `user_id` int NOT NULL,
  `comment_text` text COLLATE utf8mb4_general_ci NOT NULL,
  `image_url` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `comments`
--

INSERT INTO `comments` (`id`, `recipe_id`, `user_id`, `comment_text`, `image_url`, `created_at`) VALUES
(1, 3, 2, 'rawr', NULL, '2025-09-07 20:18:27');

-- --------------------------------------------------------

--
-- Table structure for table `favorites`
--

CREATE TABLE `favorites` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `recipe_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `favorites`
--

INSERT INTO `favorites` (`id`, `user_id`, `recipe_id`) VALUES
(1, 2, 1);

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int NOT NULL,
  `email` varchar(100) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires` bigint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `recipes`
--

CREATE TABLE `recipes` (
  `id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text,
  `ingredients` text NOT NULL,
  `instructions` text NOT NULL,
  `nutrition_info` text,
  `notes` text,
  `prep_time` int DEFAULT NULL,
  `cook_time` int DEFAULT NULL,
  `yields` varchar(100) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `author_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `recipes`
--

INSERT INTO `recipes` (`id`, `title`, `description`, `ingredients`, `instructions`, `nutrition_info`, `notes`, `prep_time`, `cook_time`, `yields`, `image_url`, `author_id`) VALUES
(1, 'Classic Vegan Pancakes', 'A timeless recipe for light, fluffy, and delicious vegan pancakes that practically melt in your mouth. The perfect canvas for all your favorite toppings.', '[\"1 ½ cups all-purpose flour\",\"2 tablespoons granulated sugar\",\"2 teaspoons baking powder\",\"½ teaspoon baking soda\",\"½ teaspoon salt\",\"1 ½ cups plant-based milk\",\"2 tablespoons apple cider vinegar\",\"2 tablespoons melted vegan butter\",\"1 teaspoon vanilla extract\"]', '[\"In a large bowl, whisk together the flour, sugar, baking powder, baking soda, and salt.\",\"In a separate bowl, combine the plant-based milk and apple cider vinegar. Let it sit for a few minutes to curdle slightly (this makes a vegan \\\"buttermilk\\\").\",\"Add the melted vegan butter and vanilla extract to the wet ingredients.\",\"Pour the wet ingredients into the dry ingredients and whisk until combined. Don\'t overmix.\",\"Heat a lightly oiled griddle or skillet over medium heat.\",\"Pour ¼ cup of batter onto the hot griddle for each pancake.\",\"Cook for 2-3 minutes per side, or until golden brown and cooked through.\",\"Serve with your favorite toppings, such as maple syrup, fresh fruit, or vegan whipped cream.\"]', '{\"Calories\":\"126\",\"Fat\":\"4g\",\"Saturated Fat\":\"1g\",\"Cholesterol\":\"0mg\",\"Sodium\":\"183mg\",\"Carbohydrates\":\"20g\",\"Dietary Fiber\":\"1g\",\"Total Sugars\":\"3g\",\"Protein\":\"3g\",\"Calcium\":\"80mg\",\"Iron\":\"1mg\",\"Potassium\":\"62mg\"}', 'The nutritional values, particularly for calcium and Vitamin D, will vary depending on the type of plant-based milk and vegan butter used. Many brands are fortified with these nutrients. These pancakes are relatively low in fat and sugar compared to traditional pancake recipes.', 10, 30, '12 pancakes', 'images/classic-pancakes.jpg', 1),
(2, 'Southwestern Tofu Scramble (oil-free)', 'A vibrant, protein-packed, and oil-free tofu scramble bursting with the flavors of the Southwest. Features black beans, corn, and a perfect blend of spices.', '[\"14 ounces extra-firm tofu, crumbled\",\"1 tablespoon vegetable broth\",\"½ cup chopped onion\",\"1 cup chopped bell pepper (red or green)\",\"1 jalapeno, minced (optional)\",\"1 teaspoon chili powder\",\"½ teaspoon cumin\",\"½ teaspoon smoked paprika\",\"Salt and black pepper to taste\",\"1 cup cooked black beans\",\"½ cup corn kernels\"]', '[\"Sauté vegetables: Heat a non-stick pan over medium heat. Add onions, and peppers, and a small amount of vegetable broth (about 1 tablespoon) to help them soften without sticking. Add jalapeno if using. Cook until softened, about 5-7 minutes.\",\"Add tofu: Crumble in tofu, cook until lightly browned, about 5-7 minutes, adding a little more broth (about 1 tablespoon at a time) if needed to prevent sticking.\",\"Season: Stir in chili powder, cumin, smoked paprika, salt, and pepper.\",\"Combine: Add black beans and corn. Heat through, about 2-3 minutes.\"]', '{\"Calories\":\"269\",\"Fat\":\"7g\",\"Saturated Fat\":\"1g\",\"Cholesterol\":\"0mg\",\"Sodium\":\"534mg\",\"Carbohydrates\":\"34g\",\"Dietary Fiber\":\"9g\",\"Total Sugars\":\"6g\",\"Protein\":\"22g\",\"Calcium\":\"384mg\",\"Iron\":\"5mg\",\"Potassium\":\"710mg\",\"Vitamin C\":\"57mg\"}', 'The sodium content is an estimate that will vary depending on the amount of added salt and the type of vegetable broth used. Using low sodium products can help to reduce sodium levels. This scramble is a good source of protein, fiber, and several vitamins and minerals.', 10, 15, '2 servings', 'images/southwestern-scramble.jpg', 1),
(3, 'Chocolate Peanut Butter Overnight Oats', 'A decadent yet healthy make-ahead breakfast. Creamy oats combined with rich cocoa and peanut butter, ready to grab and go on a busy morning.', '[\"½ cup rolled oats\",\"1 cup plant-based milk\",\"2 tablespoons peanut butter\",\"1 tablespoon cocoa powder\",\"1 tablespoon maple syrup\",\"Pinch of salt\"]', '[\"Combine all ingredients in a jar or container.\",\"Stir well and refrigerate overnight.\",\"Enjoy cold.\"]', '{\"Calories\":\"468\",\"Fat\":\"23g\",\"Saturated Fat\":\"4g\",\"Cholesterol\":\"0mg\",\"Sodium\":\"221mg\",\"Carbohydrates\":\"52g\",\"Dietary Fiber\":\"9g\",\"Total Sugars\":\"15g\",\"Protein\":\"17g\",\"Calcium\":\"342mg\",\"Iron\":\"4mg\",\"Potassium\":\"465mg\",\"Magnesium\":\"143mg\"}', 'The nutritional values will vary significantly depending on the type of plant-based milk used. This recipe is a good source of fiber, protein, and several minerals, particularly magnesium. The sugars primarily come from the maple syrup.', 5, 0, '1 serving', 'images/overnight-oats.jpg', 1);

-- --------------------------------------------------------

--
-- Table structure for table `remixes`
--

CREATE TABLE `remixes` (
  `id` int NOT NULL,
  `original_recipe_id` int NOT NULL,
  `user_id` int NOT NULL,
  `remix_title` varchar(255) NOT NULL,
  `notes` text NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `submitted_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_approved` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `remixes`
--

INSERT INTO `remixes` (`id`, `original_recipe_id`, `user_id`, `remix_title`, `notes`, `image_url`, `status`, `submitted_at`, `is_approved`) VALUES
(2, 1, 1, 'Jane\'s Zesty Lemon & Blueberry Remix', 'I followed the recipe exactly, but with two small changes that made these pancakes absolutely sing! \n\n1. I added the zest of one whole lemon to the dry ingredients. \n2. I gently folded in 1 cup of fresh blueberries right at the end before cooking. \n\nThe lemon zest adds a beautiful brightness that cuts through the sweetness, and the burst of warm blueberries in every bite is just divine. Highly recommended!', NULL, 'pending', '2025-09-07 19:44:18', 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `is_admin` tinyint(1) NOT NULL DEFAULT '0',
  `verification_token` varchar(255) DEFAULT NULL,
  `is_verified` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `is_admin`, `verification_token`, `is_verified`, `created_at`) VALUES
(1, 'ChrisAndEmma', 'chris@chrisandemmashow.com', '$2y$10$placeholderhashfortesting123', 1, NULL, 1, '2025-09-07 18:52:13'),
(2, 'Chris Cameron Tow', 'chris@chrisandinga.com', '$2y$10$7PQHUDbFT3iP7isMSE4m0.0PCF8B6KiMGkmDMm3Tx0dM9nBYprHLe', 1, NULL, 1, '2025-09-07 19:49:12');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `recipe_id` (`recipe_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `recipe_id` (`recipe_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`);

--
-- Indexes for table `recipes`
--
ALTER TABLE `recipes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `author_id` (`author_id`);

--
-- Indexes for table `remixes`
--
ALTER TABLE `remixes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `original_recipe_id` (`original_recipe_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `favorites`
--
ALTER TABLE `favorites`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `recipes`
--
ALTER TABLE `recipes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `remixes`
--
ALTER TABLE `remixes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `favorites`
--
ALTER TABLE `favorites`
  ADD CONSTRAINT `favorites_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `favorites_ibfk_2` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `recipes`
--
ALTER TABLE `recipes`
  ADD CONSTRAINT `recipes_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `remixes`
--
ALTER TABLE `remixes`
  ADD CONSTRAINT `remixes_ibfk_1` FOREIGN KEY (`original_recipe_id`) REFERENCES `recipes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `remixes_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
