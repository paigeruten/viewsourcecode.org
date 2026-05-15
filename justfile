deploy:
    ./deploy.sh

serve:
    open http://localhost:8000
    cd public && python3 -m http.server 8000
