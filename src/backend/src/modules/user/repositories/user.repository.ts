/**
 * User Repository
 * Handles all database operations for users
 */

import { IUserEntity } from '../models/user.entity';

/**
 * Mock Database (replace with real database)
 * In production, use TypeORM, Sequelize, or other ORM
 */
class UserDatabase {
  private users: Map<string, IUserEntity> = new Map();
  private emailIndex: Map<string, string> = new Map(); // email -> userId
  private usernameIndex: Map<string, string> = new Map(); // username -> userId

  async findById(id: string): Promise<IUserEntity | null> {
    return this.users.get(id) || null;
  }

  async findByEmail(email: string): Promise<IUserEntity | null> {
    const userId = this.emailIndex.get(email.toLowerCase());
    return userId ? this.users.get(userId) || null : null;
  }

  async findByUsername(username: string): Promise<IUserEntity | null> {
    const userId = this.usernameIndex.get(username.toLowerCase());
    return userId ? this.users.get(userId) || null : null;
  }

  async create(user: IUserEntity): Promise<IUserEntity> {
    this.users.set(user.id, user);
    this.emailIndex.set(user.email.toLowerCase(), user.id);
    this.usernameIndex.set(user.username.toLowerCase(), user.id);
    return user;
  }

  async update(id: string, data: Partial<IUserEntity>): Promise<IUserEntity | null> {
    const user = this.users.get(id);
    if (!user) return null;

    const updated = { ...user, ...data, updated_at: new Date() };
    this.users.set(id, updated);
    return updated;
  }

  async delete(id: string): Promise<boolean> {
    const user = this.users.get(id);
    if (!user) return false;

    this.emailIndex.delete(user.email.toLowerCase());
    this.usernameIndex.delete(user.username.toLowerCase());
    this.users.delete(id);
    return true;
  }
}

/**
 * User Repository
 * Abstraction layer for user database operations
 */
export class UserRepository {
  private db: UserDatabase;

  constructor(db?: UserDatabase) {
    this.db = db || new UserDatabase();
  }

  /**
   * Find user by ID
   */
  async findById(userId: string): Promise<IUserEntity | null> {
    try {
      return await this.db.findById(userId);
    } catch (error) {
      throw new Error(`Failed to find user by ID: ${error}`);
    }
  }

  /**
   * Find user by email
   */
  async findByEmail(email: string): Promise<IUserEntity | null> {
    try {
      return await this.db.findByEmail(email);
    } catch (error) {
      throw new Error(`Failed to find user by email: ${error}`);
    }
  }

  /**
   * Find user by username
   */
  async findByUsername(username: string): Promise<IUserEntity | null> {
    try {
      return await this.db.findByUsername(username);
    } catch (error) {
      throw new Error(`Failed to find user by username: ${error}`);
    }
  }

  /**
   * Create new user
   */
  async create(user: IUserEntity): Promise<IUserEntity> {
    try {
      return await this.db.create(user);
    } catch (error) {
      throw new Error(`Failed to create user: ${error}`);
    }
  }

  /**
   * Update user
   */
  async update(userId: string, data: Partial<IUserEntity>): Promise<IUserEntity | null> {
    try {
      return await this.db.update(userId, data);
    } catch (error) {
      throw new Error(`Failed to update user: ${error}`);
    }
  }

  /**
   * Delete user
   */
  async delete(userId: string): Promise<boolean> {
    try {
      return await this.db.delete(userId);
    } catch (error) {
      throw new Error(`Failed to delete user: ${error}`);
    }
  }

  /**
   * Check if email exists
   */
  async emailExists(email: string): Promise<boolean> {
    const user = await this.findByEmail(email);
    return user !== null;
  }

  /**
   * Check if username exists
   */
  async usernameExists(username: string): Promise<boolean> {
    const user = await this.findByUsername(username);
    return user !== null;
  }
}
