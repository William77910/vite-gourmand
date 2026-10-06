// ============================================================
// Vite & Gourmand - Initialisation MongoDB
// À exécuter avec : mongosh --file 03_init_mongodb.js
// ============================================================

db = db.getSiblingDB("vite_gourmand_stats");

// ------------------------------------------------------------
// Supprime la collection si elle existe déjà (permet de relancer
// ce script plusieurs fois sans erreur pendant le développement)
// ------------------------------------------------------------
db.commandes_stats.drop();

// ------------------------------------------------------------
// Création de la collection avec validation de schéma
// (garantit la cohérence des documents malgré le NoSQL)
// ------------------------------------------------------------
db.createCollection("commandes_stats", {
  validator: {
    $jsonSchema: {
      bsonType: "object",
      required: [
        "id_commande",
        "id_menu",
        "titre_menu",
        "montant",
        "date_commande",
        "statut",
      ],
      properties: {
        id_commande: {
          bsonType: "int",
          description: "référence vers commande.id_commande (MySQL)",
        },
        id_menu: {
          bsonType: "int",
          description: "référence vers menu.id_menu (MySQL)",
        },
        titre_menu: { bsonType: "string" },
        montant: { bsonType: ["double", "int"], minimum: 0 },
        nb_personnes: { bsonType: "int", minimum: 1 },
        date_commande: { bsonType: "date" },
        statut: {
          enum: [
            "en_attente",
            "accepte",
            "en_preparation",
            "en_cours_de_livraison",
            "livre",
            "en_attente_retour_materiel",
            "terminee",
            "annulee",
          ],
        },
      },
    },
  },
  validationLevel: "moderate", // tolère les anciens documents si le schéma évolue
});

// ------------------------------------------------------------
// Index (accélèrent les agrégations du dashboard admin)
// ------------------------------------------------------------
db.commandes_stats.createIndex({ id_menu: 1 });
db.commandes_stats.createIndex({ date_commande: 1 });
db.commandes_stats.createIndex({ statut: 1 });
db.commandes_stats.createIndex({ id_commande: 1 }, { unique: true });

// ------------------------------------------------------------
// Données de test (cohérentes avec 02_insertion_donnees.sql)
// ------------------------------------------------------------
db.commandes_stats.insertMany([
  {
    id_commande: 1,
    id_menu: 14,
    titre_menu: "Menu Barbecue",
    montant: 126.5,
    nb_personnes: 9,
    date_commande: new Date("2026-06-01T09:20:42.000Z"),
    statut: "terminee",
  },
  {
    id_commande: 2,
    id_menu: 14,
    titre_menu: "Menu Barbecue",
    montant: 199.55,
    nb_personnes: 10,
    date_commande: new Date("2026-06-09T09:26:00.000Z"),
    statut: "terminee",
  },
  {
    id_commande: 3,
    id_menu: 3,
    titre_menu: "Menu Gourmet",
    montant: 201.22,
    nb_personnes: 6,
    date_commande: new Date("2026-07-06T09:31:59.000Z"),
    statut: "annulee",
  },
  {
    id_commande: 4,
    id_menu: 1,
    titre_menu: "Menu Méditerranéen",
    montant: 105,
    nb_personnes: 4,
    date_commande: new Date("2026-07-07T09:35:34.000Z"),
    statut: "terminee",
  },
  {
    id_commande: 5,
    id_menu: 2,
    titre_menu: "Menu Végétarien",
    montant: 105,
    nb_personnes: 5,
    date_commande: new Date("2026-07-21T09:38:47.000Z"),
    statut: "terminee",
  },
  {
    id_commande: 6,
    id_menu: 2,
    titre_menu: "Menu Végétarien",
    montant: 204.55,
    nb_personnes: 7,
    date_commande: new Date("2026-08-01T09:42:35.000Z"),
    statut: "terminee",
  },
  {
    id_commande: 7,
    id_menu: 14,
    titre_menu: "Menu Barbecue",
    montant: 156.22,
    nb_personnes: 10,
    date_commande: new Date("2026-08-28T09:46:48.000Z"),
    statut: "en_cours_de_livraison",
  },
  {
    id_commande: 8,
    id_menu: 1,
    titre_menu: "Menu Méditerranéen",
    montant: 121.22,
    nb_personnes: 4,
    date_commande: new Date("2026-08-31T09:47:58.000Z"),
    statut: "en_preparation",
  },
  {
    id_commande: 9,
    id_menu: 3,
    titre_menu: "Menu Gourmet",
    montant: 244.55,
    nb_personnes: 6,
    date_commande: new Date("2026-09-01T09:50:36.000Z"),
    statut: "accepte",
  },
  {
    id_commande: 10,
    id_menu: 14,
    titre_menu: "Menu Barbecue",
    montant: 65,
    nb_personnes: 4,
    date_commande: new Date("2026-09-01T09:54:53.000Z"),
    statut: "en_attente",
  },
  {
    id_commande: 11,
    id_menu: 14,
    titre_menu: "Menu Barbecue",
    montant: 81.22,
    nb_personnes: 4,
    date_commande: new Date("2026-09-01T09:56:31.000Z"),
    statut: "en_attente",
  },
  {
    id_commande: 12,
    id_menu: 1,
    titre_menu: "Menu Méditerranéen",
    montant: 164.55,
    nb_personnes: 4,
    date_commande: new Date("2026-09-01T10:55:59.000Z"),
    statut: "en_attente",
  },
]);

print("Initialisation MongoDB terminée : collection commandes_stats prête.");
