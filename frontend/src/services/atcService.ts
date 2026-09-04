export interface AtcSetupRequest {
  card_number?: string;
  expiration_month?: string;
  expiration_year?: string;
}

export interface AtcSetupResponse {
  success: boolean;
  referenceId: string | null;
  accessToken: string | null;
  deviceDataCollectionUrl: string;
  merchantReferenceNumber: string;
  message?: string;
}

export interface AtcEnrollmentRequest {
  referenceId: string;
  fingerprintSessionId: string;
  merchantReferenceNumber: string;
  amount: number;
  currency?: string;
  card_number: string;
  expiration_month: string;
  expiration_year: string;
  cvv?: string;
  first_name?: string;
  last_name?: string;
  email?: string;
  phone?: string;
  address1?: string;
  locality?: string;
  state?: string;
  postal_code?: string;
  country?: string;
  returnUrl?: string;
}

export interface AtcEnrollmentResponse {
  success: boolean;
  isChallengeRequired: boolean;
  status: string;
  eci?: string;
  cavv?: string;
  ucafAuthenticationData?: string;
  ucafCollectionIndicator?: string;
  xid?: string;
  stepUpJwt?: string;
  acsUrl?: string;
  stepUpUrl?: string;
  authenticationTransactionId?: string;
  threeDSServerTransactionId?: string;
  specificationVersion?: string;
  message?: string;
}

export interface AtcValidateRequest {
  authenticationTransactionId: string;
  merchantReferenceNumber?: string;
}

export interface AtcValidateResponse {
  success: boolean;
  status: string;
  eci?: string;
  cavv?: string;
  ucafAuthenticationData?: string;
  ucafCollectionIndicator?: string;
  xid?: string;
  threeDSServerTransactionId?: string;
}

export interface AtcProcessPaymentRequest {
  merchantReferenceNumber: string;
  amount: number;
  currency?: string;
  card_number: string;
  expiration_month: string;
  expiration_year: string;
  cvv?: string;
  card_type?: string;
  first_name?: string;
  last_name?: string;
  email?: string;
  phone?: string;
  address1?: string;
  locality?: string;
  state?: string;
  postal_code?: string;
  country?: string;
  fingerprintSessionId?: string;
  eci?: string;
  cavv?: string;
  ucafAuthenticationData?: string;
  ucafCollectionIndicator?: string;
  xid?: string;
  threeDSServerTransactionId?: string;
  specificationVersion?: string;
  is_recurring?: boolean;
  campaign_id?: number;
  program_id?: number;
}

export interface AtcProcessPaymentResponse {
  success: boolean;
  status: string;
  transactionId?: number;
  cybersourceRequestId?: string;
  merchantReferenceNumber?: string;
  amount?: number;
  currency?: string;
  isRecurring?: boolean;
  message?: string;
  error?: unknown;
}

const getApiBaseUrl = () => {
  const url = process.env.NEXT_PUBLIC_API_BASE_URL || process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000';
  return url.endsWith('/api') ? url : `${url}/api`;
};

async function safePostJson<T>(endpoint: string, bodyData: unknown): Promise<T> {
  const controller = new AbortController();
  // 120s timeout to allow smooth execution in local environment and sandbox latency
  const timeoutId = setTimeout(() => controller.abort(), 120000);

  try {
    const res = await fetch(`${getApiBaseUrl()}${endpoint}`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify(bodyData),
      signal: controller.signal,
    });

    clearTimeout(timeoutId);

    const json = await res.json();
    if (!res.ok && !json.message) {
      json.message = json.error || `HTTP ${res.status}: Error en el servidor de pasarela.`;
    }
    return json as T;
  } catch (err: unknown) {
    clearTimeout(timeoutId);
    console.error(`[ATC Service Error] ${endpoint}:`, err);
    const errorObj = err as { name?: string; message?: string };
    if (errorObj?.name === 'AbortError') {
      return {
        success: false,
        message: 'Tiempo de espera agotado al conectar con el servidor de pagos. Por favor intente de nuevo.',
      } as unknown as T;
    }
    return {
      success: false,
      message: err.message || 'Error de conexión con el servidor de la pasarela de pagos.',
    } as unknown as T;
  }
}

export const atcService = {
  async setupAuthentication(data: AtcSetupRequest): Promise<AtcSetupResponse> {
    return safePostJson<AtcSetupResponse>('/v1/atc/setup-authentication', data);
  },

  async checkEnrollment(data: AtcEnrollmentRequest): Promise<AtcEnrollmentResponse> {
    return safePostJson<AtcEnrollmentResponse>('/v1/atc/check-enrollment', data);
  },

  async validateChallenge(data: AtcValidateRequest): Promise<AtcValidateResponse> {
    return safePostJson<AtcValidateResponse>('/v1/atc/validate-challenge', data);
  },

  async processPayment(data: AtcProcessPaymentRequest): Promise<AtcProcessPaymentResponse> {
    return safePostJson<AtcProcessPaymentResponse>('/v1/atc/process-payment', data);
  },
};
