
INSERT INTO sortie (etat_id,
                    lieu_id,
                    site_id,
                    organisateur_id,
                    nom,
                    date_heure_debut,
                    duree,
                    date_limit_inscription,
                    nb_inscriptions_max,
                    infos_sortie)
VALUES (1, 1, 1, 2, 'Sortie de Thomas', now(), '04:00:00', date(now()), 5, 'Description de la sortie.')
