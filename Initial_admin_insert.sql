-- Initial admin user insert
-- Write your information in each field
INSERT INTO `users` (`username`, `password`, `first_name`, `last_name`, `email`, `role`, `is_active`) VALUES
('admin', '$2y$10$examplehashGvHj5iKIXpOzP9H5J.W8bKlW2urCGn.D.t/UklRk1rNPKO.', 'Admin', 'User', 'admin@example.com', 'admin', 1);
