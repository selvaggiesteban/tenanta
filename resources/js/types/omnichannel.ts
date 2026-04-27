export type ChannelType = 'whatsapp' | 'messenger' | 'instagram' | 'telegram' | 'email_smtp' | 'email_gmail' | 'web_widget';

export interface ChannelCredentials {
  access_token?: string; // Meta (WhatsApp/Instagram)
  page_access_token?: string; // Messenger
  bot_token?: string; // Telegram
  verify_token?: string; // Webhook verification
  app_secret?: string; // Meta signature validation
  smtp_host?: string;
  smtp_port?: number;
  smtp_user?: string;
  smtp_pass?: string;
}

export interface ChannelSettings {
  auto_reply_enabled?: boolean;
  auto_reply_message?: string;
  webhook_url?: string;
  // Widget Customization
  primary_color?: string;
  welcome_message?: string;
  logo_url?: string;
  allowed_domains?: string; // Or string[] if you prefer, but string is easier for VTextField
}

export interface Channel {
  id: string;
  tenant_id: number;
  type: ChannelType;
  name: string;
  provider_id: string | null;
  credentials: ChannelCredentials;
  is_active: boolean;
  settings: ChannelSettings | null;
  created_at: string;
  updated_at: string;
}

export type ConversationStatus = 'open' | 'pending' | 'closed' | 'archived';

export interface Conversation {
  id: string;
  tenant_id: number;
  channel_id: string;
  contact_id: number | null;
  external_id: string;
  subject: string | null;
  status: ConversationStatus;
  assigned_to: number | null;
  last_message_at: string | null;
  metadata: Record<string, any> | null;
  created_at: string;
  updated_at: string;
  channel?: Channel;
  contact?: {
    id: number;
    name: string;
    email: string;
    phone: string;
  };
}

export type MessageDirection = 'inbound' | 'outbound';
export type MessageContentType = 'text' | 'image' | 'video' | 'document' | 'audio';
export type MessageStatus = 'sent' | 'delivered' | 'read' | 'failed';

export interface Message {
  id: string;
  conversation_id: string;
  external_id: string | null;
  direction: MessageDirection;
  sender_name: string | null;
  sender_identifier: string | null;
  content: string;
  content_type: MessageContentType;
  attachment_url: string | null;
  status: MessageStatus;
  raw_payload: any;
  created_at: string;
  updated_at: string;
}
