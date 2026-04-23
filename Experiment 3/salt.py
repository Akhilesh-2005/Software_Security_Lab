import hashlib
import os

data = {}
username = input("Create username: ")
salt = os.urandom(8).hex()

hashed_password = hashlib.sha256((input("Create password: ") + salt).encode()).hexdigest()
data[username] = (hashed_password, salt)

print("\nRegistration successful")
print("Salt generated:", salt)
print("Salted Hash:", hashed_password)
print("\nLogin Now")

login_user = input("Enter username: ")
login_pass = input("Enter password: ")

if login_user in data:
    stored_hash, stored_salt = data[login_user]
    login_hash = hashlib.sha256((login_pass + stored_salt).encode()).hexdigest()
    print("Generated hash during login:", login_hash)
    if login_hash == stored_hash:
        print("Login Successful")
    else:
        print("Wrong password")
else:
    print("Username not found")