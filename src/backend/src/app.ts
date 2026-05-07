/**
 * BadNet - Main Application Entry Point
 * Express.js Backend Server
 */

import express from 'express';
import dotenv from 'dotenv';
import cors from 'cors';

// Load environment variables
dotenv.config();

// Initialize Express app
const app = express();

// Middleware
app.use(cors());
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// Routes - Import from each module
// TODO: Import routes from each module after creation
// import userRoutes from './modules/user/routes/user.routes';
// import matchRoutes from './modules/match/routes/match.routes';
// import challengeRoutes from './modules/challenge/routes/challenge.routes';
// import teamRoutes from './modules/team/routes/team.routes';
// import tournamentRoutes from './modules/tournament/routes/tournament.routes';
// import bettingRoutes from './modules/betting/routes/betting.routes';
// import socialRoutes from './modules/social/routes/social.routes';

// Register routes
// TODO: Register routes once modules are created
// app.use('/api/users', userRoutes);
// app.use('/api/matches', matchRoutes);
// app.use('/api/challenges', challengeRoutes);
// app.use('/api/teams', teamRoutes);
// app.use('/api/tournaments', tournamentRoutes);
// app.use('/api/bets', bettingRoutes);
// app.use('/api/social', socialRoutes);

// Health check endpoint
app.get('/api/health', (req, res) => {
  res.json({ status: 'OK', message: 'BadNet Backend is running' });
});

// Error handling middleware
// TODO: Implement global error handler
// app.use(globalErrorHandler);

// 404 handler
app.use((req, res) => {
  res.status(404).json({ error: 'Route not found' });
});

// Start server
const PORT = process.env.PORT || 3001;
app.listen(PORT, () => {
  console.log(`🚀 BadNet Backend running on http://localhost:${PORT}`);
});

export default app;
