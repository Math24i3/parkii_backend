#docker stack rm api
git pull
docker-compose -f docker-compose.swarm.yml build
docker-compose -f docker-compose.swarm.yml push
#docker network create api_default
#docker stack deploy --compose-file docker-compose.swarm.yml api
