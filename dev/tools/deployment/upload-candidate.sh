#!/usr/bin/env bash
set -e

echo "Creating Tar File"
tar -czf ../Magento-rc.tar.gz .

echo "Deleting Magento-rc on live server and creating anew"
ssh -A -ttv -o StrictHostKeyChecking=no $PROXY_SERVER -p $PROXY_PORT ssh -A -ttv -o StrictHostKeyChecking=no $DEPLOYMENT_SERVER -p $DEPLOYMENT_PORT "rm -rf $DEPLOYMENT_DIR/Magento-rc"
ssh -A -ttv -o StrictHostKeyChecking=no $PROXY_SERVER -p $PROXY_PORT ssh -A -ttv -o StrictHostKeyChecking=no $DEPLOYMENT_SERVER -p $DEPLOYMENT_PORT "mkdir $DEPLOYMENT_DIR/Magento-rc"

echo "Transferring over tar file"
scp -P $DEPLOYMENT_PORT -oForwardAgent=yes -oStrictHostKeyChecking=no -oProxyCommand="ssh -Attv -W %h:%p -o StrictHostKeyChecking=no $PROXY_SERVER -p $PROXY_PORT" ../Magento-rc.tar.gz $DEPLOYMENT_SERVER:$DEPLOYMENT_DIR/Magento-rc

echo "Extracting tar file"
ssh -A -ttv -o StrictHostKeyChecking=no $PROXY_SERVER -p $PROXY_PORT ssh -A -ttv -o StrictHostKeyChecking=no $DEPLOYMENT_SERVER -p $DEPLOYMENT_PORT "tar -xzf $DEPLOYMENT_DIR/Magento-rc/Magento-rc.tar.gz -C $DEPLOYMENT_DIR/Magento-rc"
ssh -A -ttv -o StrictHostKeyChecking=no $PROXY_SERVER -p $PROXY_PORT ssh -A -ttv -o StrictHostKeyChecking=no $DEPLOYMENT_SERVER -p $DEPLOYMENT_PORT "rm $DEPLOYMENT_DIR/Magento-rc/Magento-rc.tar.gz"
