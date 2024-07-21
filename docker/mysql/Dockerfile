FROM mysql:8.0
RUN microdnf update
RUN microdnf install -y epel-release
RUN microdnf install -y wget gzip pv
COPY init.sh /init.sh
RUN chmod +x /init.sh
