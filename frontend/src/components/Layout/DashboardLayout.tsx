import { useState } from 'react';
import { Outlet, useNavigate, useLocation, Link } from 'react-router-dom';
import {
    Box,
    Drawer,
    AppBar,
    Toolbar,
    List,
    Typography,
    Divider,
    IconButton,
    ListItem,
    ListItemButton,
    ListItemIcon,
    ListItemText,
    Avatar,
    Menu,
    MenuItem,
} from '@mui/material';
import {
    Menu as MenuIcon,
    Dashboard as DashboardIcon,
    AccountBalance as AccountIcon,
    CreditCard as CardIcon,
    Payment as PaymentIcon,
    History as HistoryIcon,
    Person as PersonIcon,
    Logout as LogoutIcon,
    ChevronLeft as ChevronLeftIcon,
    AccountBalanceWallet as BalanceIcon,
    Warning as WarningIcon
} from '@mui/icons-material';
import { useAuth } from '../../context/AuthContext';
import { Badge } from '@mui/material';

const DRAWER_WIDTH = 240;

const menuItems = [
    { text: 'Dashboard', icon: <DashboardIcon />, path: '/dashboard' },
    { text: 'Saldo', icon: <BalanceIcon />, path: '/balance' },
    { text: 'Konta', icon: <AccountIcon />, path: '/accounts' },
    { text: 'Karty', icon: <CardIcon />, path: '/cards' },
    { text: 'Przelewy', icon: <PaymentIcon />, path: '/transfer' },
    { text: 'FLIK', icon: <PaymentIcon />, path: '/flik' },
    { text: 'Historia', icon: <HistoryIcon />, path: '/transactions' },
];

const getNameApp: string = import.meta.env.VITE_APP_NAME

export const DashboardLayout = () => {
    const [open, setOpen] = useState(true);
    const [anchorEl, setAnchorEl] = useState<null | HTMLElement>(null);
    const navigate = useNavigate();
    const location = useLocation();
    const { user, handleLogout } = useAuth();

    const toggleDrawer = () => setOpen(!open);

    const handleMenu = (event: React.MouseEvent<HTMLElement>) => {
        setAnchorEl(event.currentTarget);
    };

    const handleClose = () => {
        setAnchorEl(null);
    };

    const handleProfile = () => {
        navigate('/profile');
        handleClose();
    };

    const handleLogoutClick = async () => {
        await handleLogout();
        navigate('/login');
        handleClose();
    };

    return (
        <Box sx={{ display: 'flex' }}>
            <AppBar position="fixed" sx={{ zIndex: (theme) => theme.zIndex.drawer + 1, borderRadius: 0 }}>
                <Toolbar>
                    <IconButton color="inherit" edge="start" onClick={toggleDrawer} sx={{ mr: 2 }}>
                        {open ? <ChevronLeftIcon /> : <MenuIcon />}
                    </IconButton>
                    <Typography
                        variant="h6"
                        noWrap
                        component={Link}
                        to="/dashboard"
                        sx={{ textDecoration: 'none', color: 'inherit', flexGrow: 1 }}
                    >
                        🏦 {getNameApp}
                    </Typography>

                    <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
                        <Typography variant="body2" sx={{ display: { xs: 'none', sm: 'block' } }}>
                            {user?.name || user?.email}
                        </Typography>
                        <IconButton onClick={handleMenu} color="inherit">
                            <Badge
                                invisible={!!user?.email_verified_at}
                                color="warning"
                                variant="dot"
                                overlap="circular"
                                anchorOrigin={{ vertical: 'bottom', horizontal: 'right' }}
                            >
                                <Avatar sx={{ width: 32, height: 32, bgcolor: 'secondary.main' }}>
                                    {(user?.name || user?.email || '?').charAt(0).toUpperCase()}
                                </Avatar>
                            </Badge>
                        </IconButton>
                        <Menu
                            anchorEl={anchorEl}
                            open={Boolean(anchorEl)}
                            onClose={handleClose}
                            anchorOrigin={{ vertical: 'bottom', horizontal: 'right' }}
                            transformOrigin={{ vertical: 'top', horizontal: 'right' }}
                        >
                            <MenuItem onClick={handleProfile}>
                                <Badge
                                    invisible={!!user?.email_verified_at}
                                    color="warning"
                                    variant="dot"
                                    sx={{ mr: 1 }}
                                >
                                    <PersonIcon fontSize="small" />
                                </Badge>
                                Profil
                            </MenuItem>
                            <Divider />
                            <MenuItem onClick={handleLogoutClick}>
                                <LogoutIcon sx={{ mr: 1 }} fontSize="small" />
                                Wyloguj
                            </MenuItem>
                        </Menu>
                    </Box>
                </Toolbar>
            </AppBar>

            <Drawer
                variant="persistent"
                anchor="left"
                open={open}
                sx={{
                    width: DRAWER_WIDTH,
                    flexShrink: 0,
                    '& .MuiDrawer-paper': {
                        width: DRAWER_WIDTH,
                        boxSizing: 'border-box',
                        mt: 8,
                        borderRadius: 0,
                    },
                }}
            >
                <Divider />
                <List>
                    {menuItems.map((item) => (
                        <ListItem key={item.text} disablePadding>
                            <ListItemButton
                                selected={location.pathname === item.path}
                                onClick={() => navigate(item.path)}
                            >
                                <ListItemIcon>{item.icon}</ListItemIcon>
                                <ListItemText primary={item.text} />
                            </ListItemButton>
                        </ListItem>
                    ))}
                </List>
                <Divider />
                <List>
                    <ListItem disablePadding>
                        <ListItemButton
                            selected={location.pathname === '/profile'}
                            onClick={() => navigate('/profile')}
                        >
                            <ListItemIcon><PersonIcon /></ListItemIcon>
                            <ListItemText primary="Profil" />
                        </ListItemButton>
                    </ListItem>
                </List>
            </Drawer>

            <Box
                component="main"
                sx={{
                    flexGrow: 1,
                    p: 3,
                    mt: 8,
                    width: open ? `calc(100% - ${DRAWER_WIDTH}px)` : '100%',
                    transition: 'margin-left 0.3s',
                }}
            >
                <Outlet />
            </Box>
        </Box>
    );
};