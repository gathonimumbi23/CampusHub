-- Add Product Reviews Table to CampusHub Database

-- Create product_reviews table if it doesn't exist
CREATE TABLE IF NOT EXISTS `product_reviews` (
  `review_id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `reviewer_name` varchar(255) NOT NULL,
  `rating` int(11) NOT NULL CHECK (rating >= 1 AND rating <= 5),
  `review_title` varchar(255) NOT NULL,
  `review_text` text NOT NULL,
  `helpful_count` int(11) DEFAULT 0,
  `unhelpful_count` int(11) DEFAULT 0,
  `created_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`review_id`),
  KEY `product_id` (`product_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `product_reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE,
  CONSTRAINT `product_reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample reviews for demonstration
INSERT INTO `product_reviews` (`product_id`, `user_id`, `reviewer_name`, `rating`, `review_title`, `review_text`, `created_date`) VALUES
(1, 3, 'John Smith', 5, 'Great quality and fast delivery!', 'The product arrived in perfect condition. The quality is excellent and it met all my expectations. Highly recommend!', DATE_SUB(NOW(), INTERVAL 5 DAY)),
(1, 3, 'Jane Doe', 4, 'Good value for money', 'Good product at a reasonable price. Delivery was quick and the seller was very responsive.', DATE_SUB(NOW(), INTERVAL 3 DAY)),
(1, 3, 'Mike Johnson', 5, 'Exceeded expectations!', 'I was pleasantly surprised by the quality. Better than I expected for the price. Will definitely buy again!', DATE_SUB(NOW(), INTERVAL 1 DAY));
