'use client';

import React, { useEffect, useState } from 'react';

interface ThreatMetrixScriptProps {
  merchantId?: string;
  orgId?: string;
  onSessionGenerated?: (sessionId: string) => void;
}

export const ThreatMetrixScript: React.FC<ThreatMetrixScriptProps> = ({
  merchantId = 'redenlace_000021',
  orgId = '1snn5n9w', // Test org_id (Use k8vif92e for Production)
  onSessionGenerated,
}) => {
  const [sessionId, setSessionId] = useState<string>('');

  useEffect(() => {
    // Generate unique GUID for ThreatMetrix session profiling
    const randomGuid = 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, (c) => {
      const r = (Math.random() * 16) | 0;
      const v = c === 'x' ? r : (r & 0x3) | 0x8;
      return v.toString(16);
    });

    const fullSessionId = `${merchantId}${randomGuid}`;
    setSessionId(fullSessionId);

    if (onSessionGenerated) {
      onSessionGenerated(randomGuid);
    }

    // Inject ThreatMetrix script dynamically
    const scriptUrl = `https://h.online-metrix.net/fp/tags.js?org_id=${orgId}&session_id=${fullSessionId}`;
    const script = document.createElement('script');
    script.type = 'text/javascript';
    script.src = scriptUrl;
    script.async = true;
    document.head.appendChild(script);

    return () => {
      if (document.head.contains(script)) {
        document.head.removeChild(script);
      }
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [merchantId, orgId]);

  if (!sessionId) return null;

  return (
    <noscript>
      <iframe
        style={{ width: '100px', height: '100px', border: 0, position: 'absolute', top: '-5000px' }}
        src={`https://h.online-metrix.net/fp/tags?org_id=${orgId}&session_id=${sessionId}`}
      />
    </noscript>
  );
};
