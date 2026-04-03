import hashlib

password = "admin123"

hash_object = hashlib.md5(password.encode())

print("Password:", password)
print("MD5 Hash:", hash_object.hexdigest())


password = "admin123"

hash_object = hashlib.sha256(password.encode())

print("Password:", password)
print("Sha256 Hash:", hash_object.hexdigest())