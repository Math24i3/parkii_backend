#docker stack rm api
docker service update --image 127.0.0.1:5000/parkii_backend_app api_app
#docker network create api_default
#docker stack deploy --compose-file docker-compose.swarm.yml api
