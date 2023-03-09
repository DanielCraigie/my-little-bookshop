# My Little Bookshop
This project is a DynamoDB learning exercise to create a PHP App to run my Bookshop.

It's going to start as a backend CLI app to begin with while I get the DB integration & data modelling sorted.

I currently intend to bolt on a Vue.js web App to provide an interactive front end later (maybe throw some GraphQL in as well).

## Objectives

- Have fun
- Gain a better understanding of DynamoDB and NoSQL data modelling (coming from an SQL background)
- Learn how to integrate DynamoDB with PHP*

*Project will probably be ported to GO to create a Lambda driven API Gateway infrastructure later.

# Example Usage

1. `docker-compose up -d`
2. `cd bin`
3. `./createTable`
4. `./addSupplier`
5. `./listSuppliers` (copy supplier name from output)
6. `./createOrder --supplier-name <supplier name>`
7. `./listOrders` (copy order# partition key)
8. `./updateOrder --partition-key <Order PK> --books '[{"ISBN":"979-8365145672","Title":"The DynamoDB Book","Price":30.0,"Quantity":10}]'`
9. `./getOrder' --partition-key <Order PK>`
10. `./scanTable`

# Useful Queries

Select all items for PK

`aws dynamodb query --endpoint-url http://localhost:8000 --table-name bookshop --key-condition-expression "PK=:pk" --expression-attribute-values '{":pk":{"S":"supplier#9ae2b1d6-4541-47b2-a2b0-0c292e8d2a53"}}'`

Generate list of suppliers

`aws dynamodb query --endpoint-url http://localhost:8000 --table-name bookshop --index-name GSI1 --key-condition-expression "GSI1PK=:suppliers" --expression-attribute-values '{":suppliers":{"S":"suppliers"}}' --select SPECIFIC_ATTRIBUTES --projection-expression GSI1PK,GSI1SK,PK`

Retrieve details for a specific supplier

`aws dynamodb query --endpoint-url http://localhost:8000 --table-name bookshop --key-condition-expression "PK=:supplier" --expression-attribute-values '{":supplier":{"S":"supplier#9ae2b1d6-4541-47b2-a2b0-0c292e8d2a53"}}'`
