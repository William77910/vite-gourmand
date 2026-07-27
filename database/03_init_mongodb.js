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
      required: ["id_commande", "id_menu", "titre_menu", "montant", "date_commande", "statut"],
      properties: {
        id_commande: { bsonType: "int", description: "référence vers commande.id_commande (MySQL)" },
        id_menu:     { bsonType: "int", description: "référence vers menu.id_menu (MySQL)" },
        titre_menu:  { bsonType: "string" },
        montant:     { bsonType: ["double", "int"], minimum: 0 },
        nb_personnes:{ bsonType: "int", minimum: 1 },
        date_commande: { bsonType: "date" },
        statut: {
          enum: [
            "en_attente", "accepte", "en_preparation",
            "en_cours_de_livraison", "livre",
            "en_attente_retour_materiel", "terminee", "annulee"
          ]
        }
      }
    }
  },
  validationLevel: "moderate" // tolère les anciens documents si le schéma évolue
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
    id_menu: 1,
    titre_menu: "Menu Méditerranéen",
    montant: 100.00,
    nb_personnes: 4,
    date_commande: new Date("2026-07-20T14:30:00Z"),
    statut: "accepte"
  },
  {
    id_commande: 2,
    id_menu: 2,
    titre_menu: "Menu Végétarien",
    montant: 80.00,
    nb_personnes: 4,
    date_commande: new Date("2026-06-10T10:00:00Z"),
    statut: "terminee"
  },
  {
    id_commande: 3,
    id_menu: 1,
    titre_menu: "Menu Méditerranéen",
    montant: 150.00,
    nb_personnes: 6,
    date_commande: new Date("2026-05-05T18:00:00Z"),
    statut: "terminee"
  }
]);

print("Initialisation MongoDB terminée : collection commandes_stats prête.");
