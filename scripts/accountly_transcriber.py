import argparse
import sys

def main():
    parser = argparse.ArgumentParser(description='Simula la transcripción de datos financieros.')
    parser.add_argument('--file', help='Ruta del archivo a transcribir', required=True)
    parser.add_argument('--tenant', help='ID del tenant', required=True)
    
    args = parser.parse_args()
    
    print(f"Transcribiendo datos financieros para el tenant {args.tenant}...")
    
    # Simulación exitosa
    sys.exit(0)

if __name__ == "__main__":
    main()
