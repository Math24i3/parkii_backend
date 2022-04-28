git pull
docker-compose -f docker-compose.swarm.yml build
docker stack rm api
docker-compose -f docker-compose.swarm.yml push
docker stack deploy --compose-file docker-compose.swarm.yml api
