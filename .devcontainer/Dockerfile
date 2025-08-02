FROM mcr.microsoft.com/devcontainers/javascript-node:1-22-bookworm

# [Optional] Uncomment this section to install additional OS packages.
# RUN apt-get update && export DEBIAN_FRONTEND=noninteractive \
#     && apt-get -y install --no-install-recommends <your-package-list-here>

# [Optional] Uncomment if you want to install an additional version of node using nvm
# ARG EXTRA_NODE_VERSION=10
# RUN su node -c "source /usr/local/share/nvm/nvm.sh && nvm install ${EXTRA_NODE_VERSION}"

# [Optional] Uncomment if you want to install more global node modules
# RUN su node -c "npm install -g <your-package-list-here>"


# Install PHP and its common extensions, including support for PostgreSQL
RUN apt-get update && apt-get -y install --no-install-recommends php php-cli php-common php-pgsql php-mbstring php-xml php-zip php-gd php-curl php-bcmath php-tokenizer php-json php-mysql php-xmlrpc php-intl


#psql
RUN apt-get update && apt-get install -y postgresql-client