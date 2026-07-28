# DinoBot 🦖

Este é o repositório do DinoBot, um bot de WhatsApp construído em Laravel e integrado com a **Wuzapi** (tecnologia Multi-Device do WhatsApp) e com um Painel Dashboard moderno em React/Vite.

## 🚀 Como Rodar o Projeto

Como a Wuzapi é um projeto de código aberto externo, a pasta dela **não está inclusa** neste repositório por padrão. Para rodar o bot completo com Docker, siga exatamente os passos abaixo:

### Passo 1: Baixar a Wuzapi
Antes de rodar o Docker, você precisa clonar o repositório original da Wuzapi para dentro da pasta raiz do DinoBot.
No terminal, abra a pasta do DinoBot e rode o comando:
```bash
git clone https://github.com/robertsumi/wuzapi.git
```
Isso criará a pasta `wuzapi/` com todos os arquivos necessários. (A pasta `wuzapi/` já está no `.gitignore` para não conflitar com seus commits).

### Passo 2: Configurar o `.env`
Caso ainda não tenha feito, crie o arquivo `.env` na raiz do projeto (use o `.env.example` como base).
O sistema já está configurado por padrão para que o Bot e a Wuzapi se comuniquem perfeitamente usando:
```env
WUZAPI_URL=http://wuzapi:8080
```

### Passo 3: Subir os Contêineres
Com a pasta da wuzapi pronta e o `.env` configurado, basta subir os serviços usando o Docker Compose:
```bash
docker compose up -d --build
```
Isso irá construir e rodar:
- O banco de dados do bot
- O servidor da **Wuzapi** (Backend de conexão do WhatsApp)
- O **Bot (Laravel)** que escuta os webhooks e processa os comandos
- O **Dashboard (React)** para escanear o QR Code de forma visual

### Passo 4: Escanear o QR Code
1. Acesse o painel pelo navegador em: `http://localhost`
2. Entre com a senha de acesso (padrão no `.env`: `admin123`)
3. Escaneie o QR Code usando a função de Dispositivos Conectados no seu WhatsApp.

> **Importante:** Após o escaneamento, se você tiver muitas mensagens antigas, a Wuzapi irá sincronizar o histórico inteiro. Isso pode levar alguns minutos. Durante esse período inicial (History Sync), o bot pode parecer "lento" ou atrasado, mas logo após finalizar a sincronização, as respostas do bot (como `!help` e `!fig`) voltarão a ocorrer em tempo real (1 segundo).
