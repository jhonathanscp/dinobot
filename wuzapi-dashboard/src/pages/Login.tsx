import React, { useState } from 'react';
import { KeyRound, LogIn } from 'lucide-react';
import { toast } from 'sonner';

interface LoginProps {
  onLogin: () => void;
}

export default function Login({ onLogin }: LoginProps) {
  const [password, setPassword] = useState('');
  const [loading, setLoading] = useState(false);

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    
    // Simulação de login usando a senha estática do .env (injetada no build)
    const validPassword = import.meta.env.VITE_DASHBOARD_PASSWORD || 'admin';

    setTimeout(() => {
      setLoading(false);
      if (password === validPassword) {
        toast.success('Acesso autorizado');
        onLogin();
      } else {
        toast.error('Senha incorreta');
      }
    }, 600);
  };

  return (
    <div className="min-h-screen flex items-center justify-center p-4">
      <div className="glass-card w-full max-w-md p-8 animate-fade-in relative overflow-hidden">
        
        {/* Decorative elements */}
        <div className="absolute -top-20 -right-20 w-40 h-40 bg-primary/20 rounded-full blur-3xl"></div>
        <div className="absolute -bottom-20 -left-20 w-40 h-40 bg-blue-500/20 rounded-full blur-3xl"></div>

        <div className="relative z-10 flex flex-col items-center">
          <div className="w-16 h-16 bg-gradient-to-br from-primary to-blue-400 rounded-2xl flex items-center justify-center mb-6 shadow-lg shadow-primary/20">
            <KeyRound className="w-8 h-8 text-white" />
          </div>
          
          <h1 className="text-2xl font-bold mb-2">Wuzapi Dashboard</h1>
          <p className="text-zinc-400 text-sm mb-8 text-center">
            Insira a senha de acesso configurada no arquivo .env
          </p>

          <form onSubmit={handleSubmit} className="w-full space-y-4">
            <div>
              <input
                type="password"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                placeholder="Senha de acesso"
                className="input-field"
                required
              />
            </div>
            <button 
              type="submit" 
              className="btn-primary w-full flex items-center justify-center gap-2 py-3"
              disabled={loading}
            >
              {loading ? (
                <div className="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin" />
              ) : (
                <>
                  <LogIn className="w-5 h-5" />
                  Entrar
                </>
              )}
            </button>
          </form>
        </div>
      </div>
    </div>
  );
}
