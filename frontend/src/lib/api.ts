import axios, { AxiosInstance, AxiosError } from 'axios';

const API_URL = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000';

class ApiClient {
  private client: AxiosInstance;

  constructor() {
    this.client = axios.create({
      baseURL: API_URL,
      headers: {
        'Content-Type': 'application/json',
      },
      withCredentials: true,
    });

    // Request interceptor to add auth token
    this.client.interceptors.request.use(
      (config) => {
        const token = this.getToken();
        if (token) {
          config.headers.Authorization = `Bearer ${token}`;
        }
        return config;
      },
      (error) => Promise.reject(error)
    );

    // Response interceptor to handle errors
    this.client.interceptors.response.use(
      (response) => response,
      (error: AxiosError) => {
        if (error.response?.status === 401) {
          this.clearToken();
          if (typeof window !== 'undefined') {
            window.location.href = '/auth/login';
          }
        }
        return Promise.reject(error);
      }
    );
  }

  private getToken(): string | null {
    if (typeof window === 'undefined') return null;
    return localStorage.getItem('authToken');
  }

  private setToken(token: string): void {
    if (typeof window !== 'undefined') {
      localStorage.setItem('authToken', token);
    }
  }

  private clearToken(): void {
    if (typeof window !== 'undefined') {
      localStorage.removeItem('authToken');
    }
  }

  // Auth endpoints
  async register(data: { username: string; email: string; password: string; publicKey: string }) {
    const response = await this.client.post('/api/auth-new.php?action=register', data);
    if (response.data.token) {
      this.setToken(response.data.token);
    }
    return response.data;
  }

  async login(data: { email: string; password: string }) {
    const response = await this.client.post('/api/auth-new.php?action=login', data);
    if (response.data.token) {
      this.setToken(response.data.token);
    }
    return response.data;
  }

  async logout() {
    await this.client.post('/api/auth-new.php?action=logout');
    this.clearToken();
  }

  // User endpoints
  async getCurrentUser() {
    const response = await this.client.get('/api/users.php?action=current');
    return response.data;
  }

  async getUserProfile(userId: number) {
    const response = await this.client.get(`/api/users.php?action=profile&id=${userId}`);
    return response.data;
  }

  async updateProfile(data: Partial<{ full_name: string; bio: string; avatar_url: string }>) {
    const response = await this.client.post('/api/users.php?action=update', data);
    return response.data;
  }

  // Messages endpoints
  async getMessages(recipientId: number) {
    const response = await this.client.get(`/api/messages.php?action=list&recipient_id=${recipientId}`);
    return response.data;
  }

  async sendMessage(data: { recipient_id: number; content_encrypted: string; nonce: string }) {
    const response = await this.client.post('/api/messages.php?action=send', data);
    return response.data;
  }

  // Posts endpoints
  async getFeed(page: number = 1) {
    const response = await this.client.get(`/api/posts.php?action=feed&page=${page}`);
    return response.data;
  }

  async createPost(data: { content: string; visibility: 'public' | 'friends' | 'private' }) {
    const response = await this.client.post('/api/posts.php?action=create', data);
    return response.data;
  }

  async likePost(postId: number) {
    const response = await this.client.post('/api/posts.php?action=like', { post_id: postId });
    return response.data;
  }

  async commentOnPost(postId: number, content: string) {
    const response = await this.client.post('/api/posts.php?action=comment', {
      post_id: postId,
      content,
    });
    return response.data;
  }

  // Friends endpoints
  async getFriends() {
    const response = await this.client.get('/api/friends.php?action=list');
    return response.data;
  }

  async sendFriendRequest(userId: number) {
    const response = await this.client.post('/api/friends.php?action=request', { user_id: userId });
    return response.data;
  }

  async acceptFriendRequest(requestId: number) {
    const response = await this.client.post('/api/friends.php?action=accept', { request_id: requestId });
    return response.data;
  }

  async rejectFriendRequest(requestId: number) {
    const response = await this.client.post('/api/friends.php?action=reject', { request_id: requestId });
    return response.data;
  }

  // Upload media
  async uploadMedia(file: File, type: 'avatar' | 'post' | 'story') {
    const formData = new FormData();
    formData.append('file', file);
    formData.append('type', type);

    const response = await this.client.post('/api/media.php?action=upload', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });
    return response.data;
  }
}

export const api = new ApiClient();
