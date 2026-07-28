import axios from 'axios';

// Na infra do docker-compose, o Nginx servirá como proxy reverso, 
// roteando /api/wuzapi para a porta 8080 do container wuzapi
export const API_URL = import.meta.env.VITE_API_URL || '/api/wuzapi';
const TOKEN = import.meta.env.VITE_WUZAPI_TOKEN;

export const api = axios.create({
  baseURL: API_URL,
  headers: {
    'Content-Type': 'application/json',
    'Authorization': TOKEN || '',
    'token': TOKEN || '' // Wuzapi suporta ambos
  },
});

export interface ConnectionStatus {
  connected: boolean;
  loggedIn: boolean;
}

export interface WuzapiStatusResponse {
  webhook?: string;
  jid?: string;
  events?: string;
  details?: string;
}

export interface QrResponse {
  QRCode: string;
}

export const wuzapiService = {
  checkStatus: async (): Promise<ConnectionStatus> => {
    try {
      const res = await api.get('/session/status');
      
      // A API retorna res.data.data com as informações reais
      const data = res.data?.data || {};
      
      return { 
        connected: data.connected === true, 
        loggedIn: data.loggedIn === true 
      };
    } catch (e: any) {
      const msg = e.response?.data?.error || e.message;
      if (msg.includes('not connected')) return { connected: false, loggedIn: false };
      if (msg.includes('not logged in')) return { connected: true, loggedIn: false };
      return { connected: false, loggedIn: false };
    }
  },

  getQrCode: async (): Promise<string | null> => {
    try {
      const res = await api.get('/session/qr');
      return res.data?.data?.QRCode || null;
    } catch (e) {
      return null;
    }
  },

  connect: async (): Promise<boolean> => {
    try {
      await api.post('/session/connect', { Subscribe: ["Message"], Immediate: false });
      return true;
    } catch (e) {
      return false;
    }
  },

  logout: async (): Promise<boolean> => {
    try {
      await api.post('/session/logout');
      return true;
    } catch (e) {
      return false;
    }
  },

  getWebhook: async () => {
    try {
      const res = await api.get('/webhook');
      return res.data;
    } catch (e) {
      return null;
    }
  },

  setWebhook: async (webhookUrl: string, events: string[]) => {
    try {
      const res = await api.post('/webhook', {
        webhookurl: webhookUrl,
        events: events
      });
      return res.data;
    } catch (e) {
      throw e;
    }
  }
};
