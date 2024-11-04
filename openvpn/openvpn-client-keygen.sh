#!/bin/bash
KEY_DIR=/etc/openvpn/client
OUTPUT_DIR=/etc/openvpn/client
BASE_CONFIG=/etc/openvpn/client.conf
cat ${BASE_CONFIG} \
    <(echo -e '<ca>') \
    /etc/openvpn/ca.crt \
    <(echo -e '</ca>\n<cert>') \
    ${KEY_DIR}/client.crt \
    <(echo -e '</cert>\n<key>') \
    ${KEY_DIR}/client.key \
    <(echo -e '</key>\n<tls-crypt>') \
    /etc/openvpn/ta.key \
    <(echo -e '</tls-crypt>') \
    > ${OUTPUT_DIR}/client.ovpn
