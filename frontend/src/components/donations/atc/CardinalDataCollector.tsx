'use client';

import React, { useEffect, useRef } from 'react';

interface CardinalDataCollectorProps {
  jwt: string;
  collectionUrl?: string;
  onComplete?: () => void;
}

export const CardinalDataCollector: React.FC<CardinalDataCollectorProps> = ({
  jwt,
  collectionUrl = 'https://centinelapistag.cardinalcommerce.com/V1/Cruise/Collect',
  onComplete,
}) => {
  const formRef = useRef<HTMLFormElement>(null);
  const hasFiredRef = useRef<boolean>(false);

  useEffect(() => {
    // Reset ref when JWT changes
    hasFiredRef.current = false;

    // Listen for completion postMessage from Cardinal Commerce iframe
    const handleMessage = (event: MessageEvent) => {
      if (event.origin === 'https://centinelapistag.cardinalcommerce.com' || event.origin.includes('cardinalcommerce.com')) {
        console.log('[Cardinal Cruise] Data collection event:', event.data);
        if (onComplete && !hasFiredRef.current) {
          hasFiredRef.current = true;
          onComplete();
        }
      }
    };

    window.addEventListener('message', handleMessage, false);

    // Automatically submit hidden form to iframe once component mounts
    if (formRef.current && jwt) {
      formRef.current.submit();
    }

    return () => {
      window.removeEventListener('message', handleMessage);
    };
  }, [jwt, onComplete]);

  if (!jwt) return null;

  return (
    <div style={{ display: 'none' }}>
      <iframe
        id="cardinal_collection_iframe"
        name="collectionIframe"
        height="10"
        width="10"
        style={{ display: 'none' }}
      />
      <form
        ref={formRef}
        id="cardinal_collection_form"
        method="POST"
        target="collectionIframe"
        action={collectionUrl}
      >
        <input type="hidden" name="JWT" value={jwt} />
      </form>
    </div>
  );
};
