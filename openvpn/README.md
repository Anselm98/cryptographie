# OpenVPN Client Config Generator

A simple bash script that generates a unified OpenVPN client configuration file (.ovpn) by combining the base configuration snippet with necessary certificates and keys.

Playing this script will generate a `client.ovpn` file which can be used with OpenVPN clients.
## Prerequisites

- OpenVPN installed
- Required files in `/etc/openvpn/`:
  - `client.conf`
  - `ca.crt`
  - `ta.key`
  - `client/client.crt`
  - `client/client.key`

## Usage

```bash
chmod +x openvpn-client-keygen.sh
sudo ./openvpn-client-keygen.sh
```

The script will generate `client.ovpn` which can be used with OpenVPN clients.
