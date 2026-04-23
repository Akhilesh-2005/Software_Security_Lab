import hashlib

data = {}

username = input("Create username: ")
hashed_password = hashlib.sha256(input("Create password: ").encode()).hexdigest()

data[username] = hashed_password

print("Registration successful")

print("\nLogin Now")

login_user = input("Enter username: ")
login_pass = input("Enter password: ")

login_hash = hashlib.sha256(login_pass.encode()).hexdigest()

if login_user in data:
    if data[login_user] == login_hash:
        print("Login Successful")
    else:
        print("Wrong password")
else:
    print("Username not found")