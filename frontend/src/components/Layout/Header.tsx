import { Link, useNavigate } from 'react-router-dom';
import { useAuth } from '../../context/AuthContext';
import { AppBar, Toolbar, Typography, Button, Box, Avatar, IconButton, Menu, MenuItem } from '@mui/material';
import { AccountCircle, Logout } from '@mui/icons-material';
import { useState } from 'react';
import type { User } from '../../types/types';

export const Header = () => {
    const { user, handleLogout } = useAuth();
    const navigate = useNavigate();
    const [anchorEl, setAnchorEl] = useState<null | HTMLElement>(null);

    const onLogout = async (): Promise<void> => {
        await handleLogout();
        navigate('/login');
        setAnchorEl(null);
    };

    const handleMenu = (event: React.MouseEvent<HTMLElement>): void => {
        setAnchorEl(event.currentTarget);
    };

    const handleClose = (): void => {
        setAnchorEl(null);
    };

    const handleProfile = (): void => {
        navigate('/profile');
        setAnchorEl(null);
    }

    return (
        <AppBar position="fixed" sx={{ bgcolor: 'background.paper', color: 'text.primary', boxShadow: 1, borderRadius: 0 }}>
            <Toolbar>
                <Typography variant="h6" component={Link} to="/" sx={{
                    textDecoration: 'none',
                    color: 'inherit',
                    fontWeight: 'bold',
                    flexGrow: 0
                }}>
                    🏦 {import.meta.env.VITE_APP_NAME}
                </Typography>

                {user ? (
                    <Button color="inherit" component={Link} to="/balance">
                        Saldo
                    </Button>
                ) : ''}

                <Box sx={{ flexGrow: 1 }} />

                {user ? (
                    <Box sx={{ display: 'flex', alignItems: 'center', gap: 2 }}>
                        <Typography variant="body1" sx={{ display: { xs: 'none', sm: 'block' } }}>
                            Witaj, {user.name || user.email}
                        </Typography>
                        <IconButton
                            size="large"
                            aria-label="account of current user"
                            aria-controls="menu-appbar"
                            aria-haspopup="true"
                            onClick={handleMenu}
                            color="inherit"
                        >
                            <Avatar sx={{ width: 32, height: 32, bgcolor: 'primary.main' }}>
                                {(user.name || user.email).charAt(0).toUpperCase()}
                            </Avatar>
                        </IconButton>
                        <Menu
                            id="menu-appbar"
                            anchorEl={anchorEl}
                            anchorOrigin={{
                                vertical: 'top',
                                horizontal: 'right',
                            }}
                            keepMounted
                            transformOrigin={{
                                vertical: 'top',
                                horizontal: 'right',
                            }}
                            open={Boolean(anchorEl)}
                            onClose={handleClose}
                        >
                            <MenuItem onClick={handleProfile}>
                                <AccountCircle sx={{ mr: 1 }} />
                                Profil
                            </MenuItem>
                            <MenuItem onClick={onLogout}>
                                <Logout sx={{ mr: 1 }} />
                                Wyloguj
                            </MenuItem>
                        </Menu>
                    </Box>
                ) : (
                    <Box sx={{ display: 'flex', gap: 1 }}>
                        <Button color="inherit" component={Link} to="/login">
                            Logowanie
                        </Button>
                        <Button variant="contained" component={Link} to="/register">
                            Rejestracja
                        </Button>
                    </Box>
                )}
            </Toolbar>
        </AppBar>
    );
};