/**
 * User Controller
 * Handles HTTP requests for user operations
 */

import { Request, Response } from 'express';
import { UserService } from '../services/user.service';
import { validateRegisterDTO } from '../dto/register.dto';
import { validateLoginDTO } from '../dto/login.dto';
import { validateUpdateProfileDTO } from '../dto/update-profile.dto';

export class UserController {
  private userService: UserService;

  constructor(userService: UserService) {
    this.userService = userService;
  }

  /**
   * Register endpoint
   * POST /api/users/register
   */
  async register(req: Request, res: Response): Promise<void> {
    try {
      // Validate input
      const { valid, errors } = validateRegisterDTO(req.body);
      if (!valid) {
        res.status(400).json({
          success: false,
          error: 'Validation failed',
          details: errors,
        });
        return;
      }

      const { email, username, password } = req.body;

      // Register user
      const user = await this.userService.register(email, username, password);

      res.status(201).json({
        success: true,
        message: 'User registered successfully',
        data: user,
      });
    } catch (error: any) {
      if (error.message.includes('already')) {
        res.status(409).json({
          success: false,
          error: 'Conflict',
          message: error.message,
        });
      } else {
        res.status(500).json({
          success: false,
          error: 'Internal server error',
          message: error.message,
        });
      }
    }
  }

  /**
   * Login endpoint
   * POST /api/users/login
   */
  async login(req: Request, res: Response): Promise<void> {
    try {
      // Validate input
      const { valid, errors } = validateLoginDTO(req.body);
      if (!valid) {
        res.status(400).json({
          success: false,
          error: 'Validation failed',
          details: errors,
        });
        return;
      }

      const { email, password } = req.body;

      // Login user
      const loginResponse = await this.userService.login(email, password);

      res.status(200).json({
        success: true,
        message: 'Login successful',
        data: loginResponse,
      });
    } catch (error: any) {
      if (error.message.includes('Invalid') || error.message.includes('inactive')) {
        res.status(401).json({
          success: false,
          error: 'Unauthorized',
          message: error.message,
        });
      } else {
        res.status(500).json({
          success: false,
          error: 'Internal server error',
          message: error.message,
        });
      }
    }
  }

  /**
   * Get user profile endpoint
   * GET /api/users/:userId
   */
  async getProfile(req: Request, res: Response): Promise<void> {
    try {
      const { userId } = req.params;

      // Get user profile
      const user = await this.userService.getUserProfile(userId);

      if (!user) {
        res.status(404).json({
          success: false,
          error: 'Not found',
          message: 'User not found',
        });
        return;
      }

      res.status(200).json({
        success: true,
        message: 'User profile retrieved successfully',
        data: user,
      });
    } catch (error: any) {
      res.status(500).json({
        success: false,
        error: 'Internal server error',
        message: error.message,
      });
    }
  }

  /**
   * Update user profile endpoint
   * PUT /api/users/:userId
   */
  async updateProfile(req: Request, res: Response): Promise<void> {
    try {
      const { userId } = req.params;

      // Validate input
      const { valid, errors } = validateUpdateProfileDTO(req.body);
      if (!valid) {
        res.status(400).json({
          success: false,
          error: 'Validation failed',
          details: errors,
        });
        return;
      }

      // Update profile
      const updatedUser = await this.userService.updateProfile(userId, req.body);

      res.status(200).json({
        success: true,
        message: 'Profile updated successfully',
        data: updatedUser,
      });
    } catch (error: any) {
      if (error.message.includes('not found')) {
        res.status(404).json({
          success: false,
          error: 'Not found',
          message: error.message,
        });
      } else {
        res.status(500).json({
          success: false,
          error: 'Internal server error',
          message: error.message,
        });
      }
    }
  }

  /**
   * Get current user endpoint (from JWT token)
   * GET /api/users/me
   * Requires authentication middleware
   */
  async getCurrentUser(req: Request, res: Response): Promise<void> {
    try {
      // Extract user ID from request (set by auth middleware)
      const userId = (req as any).userId;

      if (!userId) {
        res.status(401).json({
          success: false,
          error: 'Unauthorized',
          message: 'No user ID in request',
        });
        return;
      }

      // Get user profile
      const user = await this.userService.getUserProfile(userId);

      if (!user) {
        res.status(404).json({
          success: false,
          error: 'Not found',
          message: 'User not found',
        });
        return;
      }

      res.status(200).json({
        success: true,
        message: 'Current user retrieved successfully',
        data: user,
      });
    } catch (error: any) {
      res.status(500).json({
        success: false,
        error: 'Internal server error',
        message: error.message,
      });
    }
  }
}
