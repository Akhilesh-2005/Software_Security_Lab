import subprocess
import os

def list_files():
    user_input = input("Enter directory name: ")
    
    if not os.path.isdir(user_input):
        print("Invalid directory!")
        return

    subprocess.run(["cmd", "/c", "dir", user_input])

if __name__ == "__main__":
    list_files()