import React, { useEffect, useState } from 'react';
import { wuzapiService, type ConnectionStatus } from '../services/api';
import { QrCode, Power, PowerOff, Activity, Webhook, RefreshCcw, LogOut } from 'lucide-react';
import { toast } from 'sonner';

interface DashboardProps {
  onLogout: () => void;
}

export default function Dashboard({ onLogout }: DashboardProps) {
  const [status, setStatus] = useState<ConnectionStatus | null>(null);
  const [qrCode, setQrCode] = useState<string | null>(null);
  const [loading, setLoading] = useState(true);
  const [actionLoading, setActionLoading] = useState(false);
  const [webhookUrl, setWebhookUrl] = useState('');
  const [webhookEvents, setWebhookEvents] = useState('Message');

  const fetchStatus = async () => {
    try {
      const currentStatus = await wuzapiService.checkStatus();
      setStatus(currentStatus);
      
      if (!currentStatus.connected || !currentStatus.loggedIn) {
        const qr = await wuzapiService.getQrCode();
        setQrCode(qr);
      } else {
        setQrCode(null);
      }

      const wh = await wuzapiService.getWebhook();
      if (wh && wh.webhook) {
        setWebhookUrl(wh.webhook);
        if (wh.subscribe) setWebhookEvents(wh.subscribe.join(','));
      }
    } catch (e) {
      console.error(e);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchStatus();
    const interval = setInterval(fetchStatus, 10000);
    return () => clearInterval(interval);
  }, []);

  const handleConnect = async () => {
    setActionLoading(true);
    const success = await wuzapiService.connect();
    if (success) {
      toast.success('Solicitação de conexão enviada');
      await fetchStatus();
    } else {
      toast.error('Falha ao conectar');
    }
    setActionLoading(false);
  };

  const handleDisconnect = async () => {
    setActionLoading(true);
    const success = await wuzapiService.logout();
    if (success) {
      toast.success('Desconectado com sucesso');
      await fetchStatus();
    } else {
      toast.error('Falha ao desconectar');
    }
    setActionLoading(false);
  };

  const handleUpdateWebhook = async (e: React.FormEvent) => {
    e.preventDefault();
    setActionLoading(true);
    try {
      await wuzapiService.setWebhook(webhookUrl, webhookEvents.split(',').map(s => s.trim()));
      toast.success('Webhook atualizado com sucesso');
    } catch (e) {
      toast.error('Erro ao atualizar webhook');
    }
    setActionLoading(false);
  };

  if (loading && !status) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="w-12 h-12 border-4 border-primary/30 border-t-primary rounded-full animate-spin" />
      </div>
    );
  }

  return (
    <div className="min-h-screen p-4 md:p-8 max-w-6xl mx-auto animate-fade-in">
      <header className="flex items-center justify-between mb-8 glass-panel p-4 md:px-8">
        <div className="flex items-center gap-3">
          <div className="w-10 h-10 bg-primary/20 rounded-lg flex items-center justify-center">
            <Activity className="text-primary w-6 h-6" />
          </div>
          <div>
            <h1 className="text-xl font-bold">Wuzapi Control</h1>
            <p className="text-xs text-zinc-400">Dashboard de Gerenciamento</p>
          </div>
        </div>
        
        <div className="flex items-center gap-4">
          <div className="flex items-center gap-2 px-3 py-1.5 rounded-full bg-black/40 border border-white/5 text-sm">
            <div className={`w-2.5 h-2.5 rounded-full ${status?.loggedIn ? 'bg-green-500 shadow-[0_0_10px_rgba(34,197,94,0.5)]' : 'bg-red-500 shadow-[0_0_10px_rgba(239,68,68,0.5)]'}`} />
            {status?.loggedIn ? 'Conectado' : 'Desconectado'}
          </div>
          <button onClick={onLogout} className="p-2 hover:bg-white/10 rounded-lg transition-colors" title="Sair">
            <LogOut className="w-5 h-5 text-zinc-400 hover:text-white" />
          </button>
        </div>
      </header>

      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        {/* Connection Status Card */}
        <div className="glass-card p-6 animate-slide-up" style={{ animationDelay: '0.1s' }}>
          <div className="flex items-center gap-3 mb-6 pb-4 border-b border-white/5">
            <QrCode className="w-5 h-5 text-blue-400" />
            <h2 className="text-lg font-semibold">Sessão do WhatsApp</h2>
          </div>
          
          <div className="flex flex-col items-center justify-center py-4 min-h-[300px]">
            {status?.loggedIn ? (
              <div className="text-center">
                <div className="w-24 h-24 bg-green-500/10 rounded-full flex items-center justify-center mx-auto mb-4 border border-green-500/20">
                  <Power className="w-12 h-12 text-green-500" />
                </div>
                <h3 className="text-xl font-medium text-white mb-2">Sessão Ativa</h3>
                <p className="text-zinc-400 text-sm mb-8">O bot está conectado e pronto para uso.</p>
                
                <button 
                  onClick={handleDisconnect} 
                  disabled={actionLoading}
                  className="btn-danger w-full max-w-xs flex items-center justify-center gap-2 mx-auto"
                >
                  <PowerOff className="w-4 h-4" /> Desconectar
                </button>
              </div>
            ) : (
              <div className="text-center w-full">
                {qrCode ? (
                  <div className="flex flex-col items-center">
                    <div className="bg-white p-4 rounded-xl shadow-2xl mb-6 w-[232px] h-[232px] flex items-center justify-center">
                      <img src={qrCode} alt="WhatsApp QR Code" className="w-[200px] h-[200px]" />
                    </div>
                    <p className="text-zinc-400 text-sm mb-6">Escaneie o QR Code no seu WhatsApp para conectar.</p>
                    <button 
                      onClick={fetchStatus}
                      className="btn-secondary flex items-center justify-center gap-2 mx-auto text-sm"
                    >
                      <RefreshCcw className="w-4 h-4" /> Atualizar QR Code
                    </button>
                  </div>
                ) : (
                  <div className="flex flex-col items-center">
                    <div className="w-20 h-20 bg-zinc-800 rounded-full flex items-center justify-center mb-6">
                      <QrCode className="w-8 h-8 text-zinc-500" />
                    </div>
                    <p className="text-zinc-400 text-sm mb-6">A sessão não está iniciada.</p>
                    <button 
                      onClick={handleConnect}
                      disabled={actionLoading}
                      className="btn-primary w-full max-w-xs flex items-center justify-center gap-2 mx-auto"
                    >
                      <Power className="w-4 h-4" /> Iniciar Conexão
                    </button>
                  </div>
                )}
              </div>
            )}
          </div>
        </div>

        {/* Webhook Settings Card */}
        <div className="glass-card p-6 animate-slide-up" style={{ animationDelay: '0.2s' }}>
          <div className="flex items-center gap-3 mb-6 pb-4 border-b border-white/5">
            <Webhook className="w-5 h-5 text-purple-400" />
            <h2 className="text-lg font-semibold">Configuração de Webhook</h2>
          </div>
          
          <form onSubmit={handleUpdateWebhook} className="space-y-5">
            <div>
              <label className="block text-sm font-medium text-zinc-400 mb-2">
                URL do Webhook
              </label>
              <input
                type="url"
                value={webhookUrl}
                onChange={(e) => setWebhookUrl(e.target.value)}
                placeholder="http://bot-app:8080/api/webhook"
                className="input-field"
                required
              />
              <p className="text-xs text-zinc-500 mt-2">
                Endereço onde a Wuzapi enviará os eventos do WhatsApp. 
                Use o nome do container da sua API (ex: http://bot-app:8080).
              </p>
            </div>
            
            <div>
              <label className="block text-sm font-medium text-zinc-400 mb-2">
                Eventos Inscritos
              </label>
              <input
                type="text"
                value={webhookEvents}
                onChange={(e) => setWebhookEvents(e.target.value)}
                placeholder="Message,ReadReceipt"
                className="input-field"
                required
              />
              <p className="text-xs text-zinc-500 mt-2">
                Eventos separados por vírgula. Para o bot, <strong>Message</strong> é obrigatório.
              </p>
            </div>

            <div className="pt-4 border-t border-white/5">
              <button 
                type="submit" 
                disabled={actionLoading}
                className="btn-primary w-full flex items-center justify-center gap-2"
              >
                {actionLoading ? (
                  <div className="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin" />
                ) : (
                  'Salvar Configurações'
                )}
              </button>
            </div>
          </form>
        </div>

      </div>
    </div>
  );
}
