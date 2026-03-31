# Usa uma imagem oficial do Node.js bem leve
FROM node:24-alpine

# Define a pasta de trabalho dentro do container
WORKDIR /usr/src/app

# Copia os arquivos de dependência e instala
COPY package*.json ./
RUN npm install

# Copia o restante do seu código (index.js)
COPY . .

# Expõe a porta 3000
EXPOSE 3000

# Comando para iniciar o bot
CMD ["npm", "start"]