INSERT INTO user (email, roles, password, nom, prenom, telephone, administrateur, actif, pseudo, site_id)
VALUES ('admin@admin.fr', '[
  "ROLE_ADMIN"
]', '$2y$13$28hfKYDt75uqyP9/IFWzy.3z5DJNZl93eOPxUJTgyfrnfQyvijxbm', 'admin',
        'admin', '0101010101', true, true, 'Admin', 1),
       ('thomas.lores2022@campus-eni.fr', '[]', '$2y$13$28hfKYDt75uqyP9/IFWzy.3z5DJNZl93eOPxUJTgyfrnfQyvijxbm', 'Lores',
        'Thomas', '0101010101', false, true, 'Toto', 1),
       ('thibaut.gerdelat2022@campus-eni.fr', '[]', '$2y$13$28hfKYDt75uqyP9/IFWzy.3z5DJNZl93eOPxUJTgyfrnfQyvijxbm',
        'Gerdelat', 'Thibaut', '0101010101', false, true, 'Titi', 1),
       ('remi.louis2022@campus-eni.fr', '[]', '$2y$13$28hfKYDt75uqyP9/IFWzy.3z5DJNZl93eOPxUJTgyfrnfQyvijxbm', 'Louis',
        'Rémi', '0101010101', false, true, 'Réré', 1),
       ('nicolas.richard2022@campus-eni.fr', '[]', '$2y$13$28hfKYDt75uqyP9/IFWzy.3z5DJNZl93eOPxUJTgyfrnfQyvijxbm',
        'Richard', 'Nicolas', '0101010101', false, true, 'Nini', 1),
       ('xavier.adenis2022@campus-eni.fr', '[]', '$2y$13$28hfKYDt75uqyP9/IFWzy.3z5DJNZl93eOPxUJTgyfrnfQyvijxbm',
        'Adenis', 'Xavier', '0101010101', false, true, 'Xaxa', 1),
       ('mickael.abin2022@campus-eni.fr', '[]', '$2y$13$28hfKYDt75uqyP9/IFWzy.3z5DJNZl93eOPxUJTgyfrnfQyvijxbm', 'Abin',
        'Mickael', '0101010101', false, true, 'Mimi', 1);