/**
 * Authentication Middleware
 * Validates JWT tokens and protects routes
 */

import { Request, Response, NextFunction } from 'express';
import * as jwt from 'jsonwebtoken';

/**
 * Extend Express Request to include userId
 */
declare global {
  namespace Express {
    interface Request {
      userId?: string;
    }
  }
}

/**
 * Authenticate token middleware
 * Verifies JWT token from Authorization header
 *
 * Usage:
 * router.get('/protected', authenticateToken, controller.method)
 */
export const authenticateToken = (req: Request, res: Response, next: NextFunction): void => {
  try {
    // Get token from Authorization header
    const authHeader = req.headers['authorization'];
    const token = authHeader && authHeader.split(' ')[1]; // Bearer TOKEN

    if (!token) {
      res.status(401).json({
        success: false,
        error: 'Unauthorized',
        message: 'No token provided',
      });
      return;
    }

    // Verify token
    const jwtSecret = process.env.JWT_SECRET || 'your_jwt_secret_key';
    const decoded = jwt.verify(token, jwtSecret) as any;

    // Attach user ID to request
    req.userId = decoded.userId;
    next();
  } catch (error: any) {
    if (error.name === 'TokenExpiredError') {
      res.status(401).json({
        success: false,
        error: 'Unauthorized',
        message: 'Token expired',
      });
    } else if (error.name === 'JsonWebTokenError') {
      res.status(401).json({
        success: false,
        error: 'Unauthorized',
        message: 'Invalid token',
      });
    } else {
      res.status(500).json({
        success: false,
        error: 'Internal server error',
        message: error.message,
      });
    }
  }
};

/**
 * Optional authentication middleware
 * Verifies token if provided, but doesn't fail if missing
 *
 * Usage:
 * router.get('/optional', optionalAuth, controller.method)
 */
export const optionalAuth = (req: Request, res: Response, next: NextFunction): void => {
  try {
    const authHeader = req.headers['authorization'];
    const token = authHeader && authHeader.split(' ')[1];

    if (token) {
      const jwtSecret = process.env.JWT_SECRET || 'your_jwt_secret_key';
      const decoded = jwt.verify(token, jwtSecret) as any;
      req.userId = decoded.userId;
    }

    next();
  } catch (error) {
    // Continue without authentication if token is invalid
    next();
  }
};

/**
 * Admin-only middleware
 * Requires user to have admin role (requires authenticateToken to be used first)
 *
 * Usage:
 * router.delete('/:id', authenticateToken, requireAdmin, controller.delete)
 */
export const requireAdmin = (req: Request, res: Response, next: NextFunction): void => {
  // This would require storing role in token
  // For now, it's a placeholder for future implementation
  const role = (req as any).userRole;

  if (role !== 'admin') {
    res.status(403).json({
      success: false,
      error: 'Forbidden',
      message: 'Admin access required',
    });
    return;
  }

  next();
};
