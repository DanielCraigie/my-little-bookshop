# My Little Bookshop
This project is a DynamoDB learning exercise to create a PHP App to run my Bookshop.

It's going to start as a backend CLI app to begin with while I get the DB integration & data modelling sorted.

I currently intend to bolt on a Vue.js web App to provide an interactive front end later (maybe throw some GraphQL in as well).

## Objectives
- Have fun
- Gain a better understanding of DynamoDB and NoSQL data modelling (coming from an SQL background)
- Learn how to integrate DynamoDB with PHP*

*Project will probably be ported to GO to create a Lambda driven API Gateway infrastructure later.

# Bootstrap
Follow these steps to setup a working environment.
1. Clone example env file: `cp .env.example .env`
   1. if you're operating in local dev env the `AWS_ACCESS_KEY_ID` & `AWS_SECRET_ACCESS_KEY` must have values (even if they're gibberish)
2. Install Composer packages: `composer install`
3. Start local DynamoDB container: `docker-compose up -d`

# Structure
- `./bin` - PHP CLI commands to execute actions (project working directory)
- `./docker/dynamodb` - persistent local storage for DynamoDB (ignored by Git)
- `./docs` - project documentation
- `./src` - PHP source code
- `./vendor` - Composer packages
- `./.bootstrap.php` - PHP bootstrapper (used by all CLI commands)
- `./.env` - PHP Environment variables

# Example Usage
1. `cd bin`
2. `./createTable`
3. `./createBook -d '{"isbn": "0-434-02110-5", "title": "Secret Service", "author": ["Christopher Andrew"], "publisher": "Heinemann - London", "genre": ["History"], "format": "hardback"}'`
3. `./createSupplier`
4. `./listSuppliers` (copy supplier name from output)
5. `./createOrder --supplier-name <supplier name>`
6. `./listOrders` (copy order# partition key)
7. `./updateOrder --partition-key <Order PK> --books '[{"ISBN":"0-434-02110-5","Price":30.0,"Quantity":10}]'`
8. `./getOrder' --partition-key <Order PK>`
9. `./scanTable`

# Useful Queries
Select all items for PK

`aws dynamodb query --endpoint-url http://localhost:8000 --table-name bookshop --key-condition-expression "PK=:pk" --expression-attribute-values '{":pk":{"S":"supplier#9ae2b1d6-4541-47b2-a2b0-0c292e8d2a53"}}'`

Generate list of suppliers

`aws dynamodb query --endpoint-url http://localhost:8000 --table-name bookshop --index-name GSI1 --key-condition-expression "GSI1PK=:suppliers" --expression-attribute-values '{":suppliers":{"S":"suppliers"}}' --select SPECIFIC_ATTRIBUTES --projection-expression GSI1PK,GSI1SK,PK`

Retrieve details for a specific supplier

`aws dynamodb query --endpoint-url http://localhost:8000 --table-name bookshop --key-condition-expression "PK=:supplier" --expression-attribute-values '{":supplier":{"S":"supplier#9ae2b1d6-4541-47b2-a2b0-0c292e8d2a53"}}'`
