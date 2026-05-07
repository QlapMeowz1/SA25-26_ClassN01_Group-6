/**
 * User Routes
 * Defines all routes for user module
 */

import { Router, Request, Response } from 'express';
import { UserController } from '../controllers/user.controller';
import { UserService } from '../services/user.service';
import { UserRepository } from '../repositories/user.repository';
import { authenticateToken } from '../../../middleware/auth.middleware';

// Initialize repository, service, and controller
const userRepository = new UserRepository();
const userService = new UserService(userRepository);
const userController = new UserController(userService);

// Create router
const router = Router();

/**
 * Auth routes
 */

/**
 * POST /api/users/register
 * Register a new user
 *
 * Request body:
 * {
 *   "email": "user@example.com",
 *   "username": "username123",
 *   "password": "SecurePass123",
 *   "confirmPassword": "SecurePass123"
 * }
 *
 * Response: 201 Created
 */
router.post('/register', (req: Request, res: Response) => {
  userController.register(req, res);
});

/**
 * POST /api/users/login
 * Login user and get JWT token
 *
 * Request body:
 * {
 *   "email": "user@example.com",
 *   "password": "SecurePass123"
 * }
 *
 * Response: 200 OK
 * {
 *   "accessToken": "jwt_token_here",
 *   "user": { ... }
 * }
 */
router.post('/login', (req: Request, res: Response) => {
  userController.login(req, res);
});

/**
 * Protected routes (require authentication)
 */

/**
 * GET /api/users/me
 * Get current authenticated user
 * Requires: Authorization header with Bearer token
 *
 * Response: 200 OK
 */
router.get('/me', authenticateToken, (req: Request, res: Response) => {
  userController.getCurrentUser(req, res);
});

/**
 * GET /api/users/:userId
 * Get user profile by ID (public)
 *
 * Parameters:
 * - userId: UUID of the user
 *
 * Response: 200 OK
 */
router.get('/:userId', (req: Request, res: Response) => {
  userController.getProfile(req, res);
});

/**
 * PUT /api/users/:userId
 * Update user profile
 * Requires: Authentication (user can only update their own profile)
 *
 * Parameters:
 * - userId: UUID of the user
 *
 * Request body (all fields optional):
 * {
 *   "first_name": "John",
 *   "last_name": "Doe",
 *   "bio": "Badminton player",
 *   "avatar_url": "https://...",
 *   "phone": "+1234567890",
 *   "location": "New York"
 * }
 *
 * Response: 200 OK
 */
router.put('/:userId', authenticateToken, (req: Request, res: Response) => {
  // Optional: Verify that user can only update their own profile
  const userId = (req as any).userId;
  if (userId !== req.params.userId) {
    res.status(403).json({
      success: false,
      error: 'Forbidden',
      message: 'You can only update your own profile',
    });
    return;
  }

  userController.updateProfile(req, res);
});

export default router;
