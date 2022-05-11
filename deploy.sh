#docker stack rm api
git pull
docker-compose -f docker-compose.swarm.yml build
docker-compose -f docker-compose.swarm.yml push
docker service update --image 127.0.0.1:5000/parkii_backend_app api_app
#docker network create api_default
#docker stack deploy --compose-file docker-compose.swarm.yml api
