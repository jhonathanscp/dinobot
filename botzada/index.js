require('dotenv').config();
const express = require('express');
const axios = require('axios');

const app = express();
const port = process.env.PORT || 3000;

app.use(express.json({ limit: '50mb' }));

const evolutionApiUrl = process.env.EVOLUTION_API_URL || 'http://localhost:8080';
const apiKey = process.env.EVOLUTION_API_KEY || 'senha_super_segura_api';
const instanceName = process.env.INSTANCE_NAME || 'botzada';

const api = axios.create({
  baseURL: evolutionApiUrl,
  headers: {
    'apikey': apiKey,
    'Content-Type': 'application/json'
  }
});

app.post('/webhook', async (req, res) => {
  const payload = req.body;
  
  if (payload.event === 'messages.upsert') {
    const data = payload.data;
    if (!data) return res.send('OK');

    const messageType = data.messageType;
    const remoteJid = data.key.remoteJid;
    const fromMe = data.key.fromMe;
    
    // Ignora mensagens enviadas pelo proprio bot
    if (fromMe) return res.send('OK');

    // Ignora mensagens de status
    if (remoteJid === 'status@broadcast') return res.send('OK');

    let textStr = '';
    if (messageType === 'conversation') {
      textStr = data.message?.conversation || '';
    } else if (messageType === 'extendedTextMessage') {
      textStr = data.message?.extendedTextMessage?.text || '';
    } else if (messageType === 'imageMessage') {
      textStr = data.message?.imageMessage?.caption || '';
    }
    
    // Verifica prefixo '!'
    if (!textStr || !textStr.startsWith('!')) return res.send('OK');

    const [command, ...args] = textStr.trim().split(' ');

    console.log(`Comando recebido: ${command} de ${remoteJid}`);

    if (command === '!help') {
      const helpText = `*🤖 BotzadaGames - Menu 🤖*\n\n` +
        `*!help* - Mostra todos os comandos do bot.\n` +
        `*!fig* - Envie uma imagem com este comando (ou responda a uma imagem com ele) para criar uma figurinha.`;
        
      try {
        await api.post(`/message/sendText/${instanceName}`, {
          number: remoteJid,
          text: helpText,
          delay: 1000
        });
      } catch (err) {
        console.error("Erro ao enviar help:", err?.response?.data || err?.message);
      }
    }

    if (command === '!fig') {
      try {
        let targetMessageObj = null;

        // Se a imagem veio com legenda da mensagem
        if (messageType === 'imageMessage') {
          targetMessageObj = data.message;
        } 
        // Se a pessoa respondeu a outra mensagem com o comando
        else if (messageType === 'extendedTextMessage') {
          const contextInfo = data.message?.extendedTextMessage?.contextInfo;
          if (contextInfo?.quotedMessage?.imageMessage) {
            targetMessageObj = contextInfo.quotedMessage;
          }
        }

        if (targetMessageObj) {
          // Solicita o base64
          const base64Res = await api.post(`/chat/getBase64FromMediaMessage/${instanceName}`, {
            message: targetMessageObj
          });
          
          if (base64Res.data && base64Res.data.base64) {
            const base64Str = base64Res.data.base64;
            
            // Envia a figurinha usando o base64
            await api.post(`/message/sendSticker/${instanceName}`, {
              number: remoteJid,
              sticker: base64Str,
              delay: 800
            });
            console.log(`Figurinha enviada para ${remoteJid}`);
          }
        } else {
           await api.post(`/message/sendText/${instanceName}`, {
              number: remoteJid,
              text: "⚠️ AVISO: Comando incorreto. Você deve enviar o comando *!fig* na legenda de uma imagem ou responder a uma imagem existente com *!fig*.",
              delay: 500
           });
        }
      } catch (err) {
        console.error("Erro ao gerar figurinha:", err?.response?.data || err.message);
      }
    }
  }

  res.send('EVENT_RECEIVED');
});

app.listen(port, () => {
  console.log(`🤖 BotzadaGames em execução na porta ${port}`);
  console.log(`👉 Webhook URL configurável: http://localhost:${port}/webhook`);
});
