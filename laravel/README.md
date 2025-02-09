📌 Finance Wallet

📖 Sobre o Projeto

O Finance Wallet é uma API de carteira financeira desenvolvida com Laravel 10, utilizando Docker e PostgreSQL. O objetivo é permitir que usuários realizem transferências e depósitos de dinheiro de forma segura e confiável, garantindo a validação de saldo e reversão de operações quando necessário.

🚀 Tecnologias Utilizadas

Laravel 10 - Framework PHP

Docker - Contêinerização do ambiente

PostgreSQL - Banco de dados relacional

PHP 8.2 - Versão do PHP utilizada

Composer - Gerenciador de dependências PHP

NGINX - Servidor Web

🔧 Instalação e Configuração

1️⃣ Clonar o repositório

git clone https://github.com/seu-usuario/finance-wallet.git
cd finance-wallet

2️⃣ Criar o ambiente com Docker

docker-compose up -d --build

3️⃣ Acessar o container Laravel

docker exec -it finance_wallet_app bash

4️⃣ Configurar Laravel

composer install
cp .env.example .env
php artisan key:generate

5️⃣ Configurar o Banco de Dados

Edite o arquivo .env:

DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=finance_wallet
DB_USERNAME=user
DB_PASSWORD=password

6️⃣ Rodar as migrações

php artisan migrate

🔗 Endpoints da API

🔹 Criar um usuário

POST /api/register

Body:

{
  "name": "João Silva",
  "email": "joao@email.com",
  "password": "123456"
}

🔹 Autenticar usuário

POST /api/login

Body:

{
  "email": "joao@email.com",
  "password": "123456"
}

🔹 Realizar transferência

POST /api/transfer

Body:

{
  "sender_id": 1,
  "receiver_id": 2,
  "amount": 100.00
}

🔹 Realizar depósito

POST /api/deposit

Body:

{
  "user_id": 1,
  "amount": 200.00
}

✅ Testes

Para rodar os testes automatizados:

docker exec -it finance_wallet_app php artisan test

🛠 Ferramentas Extras

Observabilidade: Logs e monitoramento configurados.

Testes de Integração e Unitários: Garantindo a qualidade do código.

Segurança: Uso de autenticação JWT e validação de transações.

🚀 Desenvolvido por Sophia Victória

