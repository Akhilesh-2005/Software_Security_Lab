import mysql.connector

DB_HOST = "localhost"
DB_USER = "admin"
DB_PASSWORD = "admin123"
DB_NAME = "company_db"

connection = mysql.connector.connect(
    host=DB_HOST,
    user=DB_USER,
    password=DB_PASSWORD,
    database=DB_NAME
)

print("Connected successfully")