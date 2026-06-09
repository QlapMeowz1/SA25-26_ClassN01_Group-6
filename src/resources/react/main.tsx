import React from 'react';
import { createRoot } from 'react-dom/client';

function PlayerPortalFallback() {
    const root = document.getElementById('root');
    const userName = root?.dataset.userName || 'Player';
    const rank = root?.dataset.userRank || 'Beginner';

    return (
        <main style={{
            minHeight: '100%',
            display: 'grid',
            placeItems: 'center',
            background: '#0a0c10',
            color: '#f8fafc',
            fontFamily: 'Inter, system-ui, sans-serif',
            padding: 24,
        }}>
            <section style={{
                width: 'min(720px, 100%)',
                border: '1px solid rgba(200,245,58,.28)',
                borderRadius: 24,
                padding: 28,
                background: '#111827',
                boxShadow: '0 24px 80px rgba(0,0,0,.28)',
            }}>
                <p style={{
                    margin: '0 0 10px',
                    color: '#c8f53a',
                    fontWeight: 800,
                    letterSpacing: '.14em',
                    textTransform: 'uppercase',
                    fontSize: 12,
                }}>
                    SMASH Player Portal
                </p>
                <h1 style={{
                    margin: 0,
                    fontSize: 46,
                    lineHeight: .95,
                    fontFamily: '"Barlow Condensed", Inter, sans-serif',
                    textTransform: 'uppercase',
                }}>
                    Welcome, {userName}
                </h1>
                <p style={{ color: '#94a3b8', fontWeight: 700 }}>
                    Rank: {rank}. The Laravel Blade portal is the active experience for dashboard, matches, betting, teams, and tournaments.
                </p>
                <a href="/dashboard" style={{
                    display: 'inline-flex',
                    minHeight: 44,
                    alignItems: 'center',
                    borderRadius: 999,
                    background: '#c8f53a',
                    color: '#071008',
                    padding: '0 18px',
                    textDecoration: 'none',
                    fontWeight: 900,
                }}>
                    Open Dashboard
                </a>
            </section>
        </main>
    );
}

const element = document.getElementById('root');

if (element) {
    createRoot(element).render(<PlayerPortalFallback />);
}
