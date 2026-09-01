'use client';

import React, { useEffect, useRef, useState } from 'react';
import { createPortal } from 'react-dom';

interface StepUpChallengeModalProps {
  isOpen: boolean;
  stepUpJwt: string;
  stepUpUrl?: string;
  onSuccess: () => void;
  onCancel: () => void;
}

export const StepUpChallengeModal: React.FC<StepUpChallengeModalProps> = ({
  isOpen,
  stepUpJwt,
  stepUpUrl = 'https://centinelapistag.cardinalcommerce.com/V2/Cruise/StepUp',
  onSuccess,
  onCancel,
}) => {
  const formRef = useRef<HTMLFormElement>(null);
  const [mounted, setMounted] = useState(false);

  useEffect(() => {
    setMounted(true);
  }, []);

  useEffect(() => {
    if (isOpen && formRef.current && stepUpJwt) {
      formRef.current.submit();
    }
  }, [isOpen, stepUpJwt]);

  if (!isOpen || !stepUpJwt || !mounted) return null;

  const modalContent = (
    <div className="fixed inset-0 z-[99999] flex items-center justify-center bg-black/70 backdrop-blur-md p-4">
      <div className="bg-white rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden border border-gray-100 animate-in fade-in zoom-in duration-200">
        <div className="bg-gradient-to-r from-blue-600 to-indigo-700 p-4 text-white flex justify-between items-center">
          <div className="flex items-center space-x-2">
            <svg className="w-5 h-5 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 002-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
            <h3 className="font-semibold text-base">Verificación 3D Secure 2.0</h3>
          </div>
          <button
            onClick={onCancel}
            className="text-blue-100 hover:text-white transition-colors text-lg"
          >
            ✕
          </button>
        </div>

        <div className="p-4 flex flex-col items-center">
          <p className="text-sm text-gray-600 mb-3 text-center">
            Tu banco emisor requiere confirmar tu identidad para validar la donación.
          </p>

          <iframe
            name="step-up-iframe"
            className="w-full h-[400px] border border-gray-200 rounded-lg shadow-inner"
          />

          <form
            ref={formRef}
            id="step-up-form"
            target="step-up-iframe"
            method="POST"
            action={stepUpUrl}
            className="hidden"
          >
            <input type="hidden" name="JWT" value={stepUpJwt} />
          </form>

          <div className="mt-4 flex space-x-3 w-full">
            <button
              onClick={onCancel}
              className="flex-1 py-2 px-4 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50 transition-colors font-medium"
            >
              Cancelar
            </button>
            <button
              onClick={onSuccess}
              className="flex-1 py-2 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg text-sm transition-colors shadow-sm"
            >
              Ya completé la verificación
            </button>
          </div>
        </div>
      </div>
    </div>
  );

  return createPortal(modalContent, document.body);
};
