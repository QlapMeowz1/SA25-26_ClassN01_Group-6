/**
 * User Service
 * Contains business logic for user operations
 */

import { v4 as uuidv4 } from 'uuid';
import * as bcrypt from 'bcrypt';
import * as jwt from 'jsonwebtoken';
import { UserRepository } from '../repositories/user.repository';
import { UserEntity, IUserEntity } from '../models/user.entity';
import { IUserProfile, IAuthPayload, IJWTPayload, ILoginResponse } from '../models/user.interface';

export class UserService {
  private userRepository: UserRepository;
  private jwtSecret: string;
  private jwtExpiration: string;

  constructor(
    userRepository: UserRepository,
    jwtSecret: string = process.env.JWT_SECRET || 'your_jwt_secret_key',
    jwtExpiration: string = process.env.JWT_EXPIRE || '7d'
  ) {
    this.userRepository = userRepository;
    this.jwtSecret = jwtSecret;
    this.jwtExpiration = jwtExpiration;
  }

  /**
   * Register a new user
   * @param email - User email
   * @param username - User username
   * @param password - User password (will be hashed)
   * @returns Created user (without password)
   */
  async register(email: string, username: string, password: string): Promise<IUserProfile> {
    // Check if email already exists
    const existingEmail = await this.userRepository.findByEmail(email);
    if (existingEmail) {
      throw new Error('Email already registered');
    }

    // Check if username already exists
    const existingUsername = await this.userRepository.findByUsername(username);
    if (existingUsername) {
      throw new Error('Username already taken');
    }

    // Hash password
    const passwordHash = await this.hashPassword(password);

    // Create user entity
    const user = new UserEntity({
      id: uuidv4(),
      email: email.toLowerCase(),
      username: username.toLowerCase(),
      password_hash: passwordHash,
      first_name: null,
      last_name: null,
      bio: null,
      avatar_url: null,
      phone: null,
      location: null,
      rating: 1200,
      wins: 0,
      losses: 0,
      is_active: true,
      is_verified: false,
      role: 'user',
      followers_count: 0,
      following_count: 0,
      created_at: new Date(),
      updated_at: new Date(),
      last_login_at: null,
    });

    // Save user to database
    const savedUser = await this.userRepository.create(user);

    return this.userToProfile(savedUser);
  }

  /**
   * Login user and return JWT token
   * @param email - User email
   * @param password - User password
   * @returns Login response with token and user data
   */
  async login(email: string, password: string): Promise<ILoginResponse> {
    // Find user by email
    const user = await this.userRepository.findByEmail(email);
    if (!user) {
      throw new Error('Invalid email or password');
    }

    // Verify password
    const isPasswordValid = await this.verifyPassword(password, user.password_hash);
    if (!isPasswordValid) {
      throw new Error('Invalid email or password');
    }

    // Check if account is active
    if (!user.is_active) {
      throw new Error('Account is inactive');
    }

    // Update last login time
    await this.userRepository.update(user.id, {
      last_login_at: new Date(),
    });

    // Generate JWT token
    const token = this.generateToken({
      userId: user.id,
      email: user.email,
      username: user.username,
      role: user.role,
    });

    return {
      accessToken: token,
      user: this.userToProfile(user),
    };
  }

  /**
   * Get user profile by ID
   * @param userId - User ID
   * @returns User profile or null if not found
   */
  async getUserProfile(userId: string): Promise<IUserProfile | null> {
    const user = await this.userRepository.findById(userId);
    if (!user) {
      return null;
    }

    return this.userToProfile(user);
  }

  /**
   * Update user profile
   * @param userId - User ID
   * @param updates - Fields to update
   * @returns Updated user profile
   */
  async updateProfile(
    userId: string,
    updates: {
      first_name?: string;
      last_name?: string;
      bio?: string;
      avatar_url?: string;
      phone?: string;
      location?: string;
    }
  ): Promise<IUserProfile> {
    const user = await this.userRepository.findById(userId);
    if (!user) {
      throw new Error('User not found');
    }

    // Filter out undefined values
    const validUpdates = Object.fromEntries(
      Object.entries(updates).filter(([, value]) => value !== undefined)
    );

    // Update user
    const updatedUser = await this.userRepository.update(userId, validUpdates);
    if (!updatedUser) {
      throw new Error('Failed to update user');
    }

    return this.userToProfile(updatedUser);
  }

  /**
   * Verify JWT token
   * @param token - JWT token
   * @returns Decoded token payload
   */
  verifyToken(token: string): IJWTPayload {
    try {
      const decoded = jwt.verify(token, this.jwtSecret) as IJWTPayload;
      return decoded;
    } catch (error) {
      throw new Error('Invalid or expired token');
    }
  }

  /**
   * Hash password using bcrypt
   * @param password - Plain text password
   * @returns Hashed password
   */
  private async hashPassword(password: string): Promise<string> {
    const saltRounds = 10;
    return bcrypt.hash(password, saltRounds);
  }

  /**
   * Verify password against hash
   * @param password - Plain text password
   * @param hash - Password hash
   * @returns True if password matches hash
   */
  private async verifyPassword(password: string, hash: string): Promise<boolean> {
    return bcrypt.compare(password, hash);
  }

  /**
   * Generate JWT token
   * @param payload - Token payload
   * @returns JWT token
   */
  private generateToken(payload: IAuthPayload): string {
    return jwt.sign(payload, this.jwtSecret, {
      expiresIn: this.jwtExpiration,
    });
  }

  /**
   * Convert user entity to profile (remove sensitive data)
   * @param user - User entity
   * @returns User profile
   */
  private userToProfile(user: IUserEntity): IUserProfile {
    const winRate =
      user.wins + user.losses > 0 ? Math.round((user.wins / (user.wins + user.losses)) * 100) : 0;

    return {
      id: user.id,
      email: user.email,
      username: user.username,
      first_name: user.first_name,
      last_name: user.last_name,
      bio: user.bio,
      avatar_url: user.avatar_url,
      phone: user.phone,
      location: user.location,
      rating: user.rating,
      wins: user.wins,
      losses: user.losses,
      is_active: user.is_active,
      is_verified: user.is_verified,
      followers_count: user.followers_count,
      following_count: user.following_count,
      created_at: user.created_at,
      updated_at: user.updated_at,
      last_login_at: user.last_login_at,
      win_rate: winRate,
    };
  }
}
