import os

def list_files():
    user_input = input("Enter directory name: ")
    
    os.system("dir " + user_input)

if __name__ == "__main__":
    list_files()