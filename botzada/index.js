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
  // 1. Libera a Evolution API imediatamente para não travar a fila
  res.send('EVENT_RECEIVED');

  const payload = req.body;
  
  if (payload.event === 'messages.upsert') {
    const data = payload.data;
    if (!data) return;

    const messageType = data.messageType;
    const remoteJid = data.key.remoteJid;
    const fromMe = data.key.fromMe;
    
    // Ignora mensagens enviadas pelo proprio bot e mensagens de status
    if (fromMe || remoteJid === 'status@broadcast') return;

    let textStr = '';
    if (messageType === 'conversation') {
      textStr = data.message?.conversation || '';
    } else if (messageType === 'extendedTextMessage') {
      textStr = data.message?.extendedTextMessage?.text || '';
    } else if (messageType === 'imageMessage') {
      textStr = data.message?.imageMessage?.caption || '';
    }
    
    // Se não tem texto ou não começa com '!', encerra aqui
    if (!textStr || !textStr.startsWith('!')) return;

    const [command, ...args] = textStr.trim().split(' ');
    console.log(`Comando recebido: ${command} de ${remoteJid}`);

    // --- COMANDO !HELP ---
    if (command === '!help') {
      const helpText = `*🤖 BotzadaGames - Menu 🤖*\n\n*!help* - Mostra todos os comandos do bot.\n*!fig* - Envie uma imagem com este comando (ou responda a uma imagem) para criar figurinha.`;
      try {
        await api.post(`/message/sendText/${instanceName}`, {
          number: remoteJid,
          text: helpText,
          delay: 1000
        });
      } catch (err) {
        console.error("❌ Erro ao enviar help:", err?.message);
      }
    }

    // --- COMANDO !FIG ---
    if (command === '!fig') {
      try {
        let messageToDownload = null;

        if (messageType === 'imageMessage') {
          // Se a imagem veio direto, mandamos o objeto data inteiro
          messageToDownload = data;
        } else if (messageType === 'extendedTextMessage') {
          const contextInfo = data.message?.extendedTextMessage?.contextInfo;
          if (contextInfo?.quotedMessage?.imageMessage) {
            // Se respondeu a uma imagem, montamos a estrutura exata que a API exige
            messageToDownload = {
              key: {
                remoteJid: remoteJid,
                id: contextInfo.stanzaId, // Pega o ID da mensagem original que tem a foto
                participant: contextInfo.participant
              },
              message: contextInfo.quotedMessage
            };
          }
        }

        if (messageToDownload) {
          // Solicita o base64
          const base64Res = await api.post(`/chat/getBase64FromMediaMessage/${instanceName}`, {
            message: messageToDownload
          });
          
          if (base64Res.data && base64Res.data.base64) {
            // Envia a figurinha
            await api.post(`/message/sendSticker/${instanceName}`, {
              number: remoteJid,
              sticker: base64Res.data.base64,
              delay: 800
            });
            console.log(`✅ Figurinha enviada com sucesso para ${remoteJid}`);
          }
        } else {
           await api.post(`/message/sendText/${instanceName}`, {
              number: remoteJid,
              text: "⚠️ AVISO: Você deve enviar o comando *!fig* na legenda de uma imagem ou responder a uma imagem existente com o comando.",
              delay: 500
           });
        }
      } catch (err) {
        console.error("❌ Erro ao gerar figurinha:", err?.response?.data || err.message);
      }
    }
  }
});

app.listen(port, () => {
  console.log(`🤖 BotzadaGames em execução na porta ${port}`);
});