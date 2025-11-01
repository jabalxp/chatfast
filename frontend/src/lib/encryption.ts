import nacl from 'tweetnacl';
import { decodeUTF8, encodeUTF8, encodeBase64, decodeBase64 } from 'tweetnacl-util';

export interface KeyPair {
  publicKey: string;
  secretKey: string;
}

export interface EncryptedMessage {
  ciphertext: string;
  nonce: string;
}

/**
 * Generate a new key pair for end-to-end encryption
 */
export function generateKeyPair(): KeyPair {
  const keyPair = nacl.box.keyPair();
  return {
    publicKey: encodeBase64(keyPair.publicKey),
    secretKey: encodeBase64(keyPair.secretKey),
  };
}

/**
 * Encrypt a message for a recipient
 * @param message - Plain text message to encrypt
 * @param recipientPublicKey - Recipient's public key (base64)
 * @param senderSecretKey - Sender's secret key (base64)
 */
export function encryptMessage(
  message: string,
  recipientPublicKey: string,
  senderSecretKey: string
): EncryptedMessage {
  const nonce = nacl.randomBytes(nacl.box.nonceLength);
  const messageUint8 = decodeUTF8(message);
  const recipientKeyUint8 = decodeBase64(recipientPublicKey);
  const secretKeyUint8 = decodeBase64(senderSecretKey);

  const encrypted = nacl.box(
    messageUint8,
    nonce,
    recipientKeyUint8,
    secretKeyUint8
  );

  if (!encrypted) {
    throw new Error('Encryption failed');
  }

  return {
    ciphertext: encodeBase64(encrypted),
    nonce: encodeBase64(nonce),
  };
}

/**
 * Decrypt a message from a sender
 * @param encryptedData - Encrypted message data
 * @param senderPublicKey - Sender's public key (base64)
 * @param recipientSecretKey - Recipient's secret key (base64)
 */
export function decryptMessage(
  encryptedData: EncryptedMessage,
  senderPublicKey: string,
  recipientSecretKey: string
): string {
  const nonce = decodeBase64(encryptedData.nonce);
  const ciphertext = decodeBase64(encryptedData.ciphertext);
  const senderKeyUint8 = decodeBase64(senderPublicKey);
  const secretKeyUint8 = decodeBase64(recipientSecretKey);

  const decrypted = nacl.box.open(
    ciphertext,
    nonce,
    senderKeyUint8,
    secretKeyUint8
  );

  if (!decrypted) {
    throw new Error('Decryption failed - message may be corrupted or keys are incorrect');
  }

  return encodeUTF8(decrypted);
}

/**
 * Store key pair in localStorage (for browser)
 */
export function storeKeyPair(keyPair: KeyPair): void {
  if (typeof window !== 'undefined') {
    localStorage.setItem('publicKey', keyPair.publicKey);
    localStorage.setItem('secretKey', keyPair.secretKey);
  }
}

/**
 * Retrieve key pair from localStorage
 */
export function getStoredKeyPair(): KeyPair | null {
  if (typeof window === 'undefined') return null;

  const publicKey = localStorage.getItem('publicKey');
  const secretKey = localStorage.getItem('secretKey');

  if (!publicKey || !secretKey) return null;

  return { publicKey, secretKey };
}

/**
 * Clear stored keys from localStorage
 */
export function clearStoredKeys(): void {
  if (typeof window !== 'undefined') {
    localStorage.removeItem('publicKey');
    localStorage.removeItem('secretKey');
  }
}
