🔹 Création de la structure du projet pour Heroku :

cd ..
mkdir mon-app && cd mon-app
mkdir public_html
touch composer.json Procfile .gitignore

🔹 Ajout du contenu aux fichiers :

echo '{
  "require": {
    "php": "^8.0"
  }
}' > composer.json

echo 'web: heroku-php-apache2 public_html/' > Procfile

echo '*.log
.DS_Store
node_modules/
vendor/' > .gitignore

🔹 Ajout des fichiers HTML et PHP dans le dossier public_html
💾 Lien de la page HTML sur GitHub : Camhack

🔹 Configuration Composer :

composer install
