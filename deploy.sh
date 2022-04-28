git pull
docker stack rm api
docker-compose -f docker-compose.swarm.yml build
docker-compose -f docker-compose.swarm.yml push
docker stack deploy --compose-file docker-compose.swarm.yml api
