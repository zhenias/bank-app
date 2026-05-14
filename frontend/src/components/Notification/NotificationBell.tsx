import { useState, useEffect, useCallback } from 'react';
import { useNavigate } from 'react-router-dom';
import {
    Badge,
    IconButton,
    Menu,
    MenuItem,
    Typography,
    Box,
    Divider,
    Button,
    ListItemText,
    ListItemIcon,
} from '@mui/material';
import {
    Notifications as NotificationsIcon,
    Circle as CircleIcon,
    ArrowForward as ViewAllIcon,
} from '@mui/icons-material';
import { useAuth } from '../../context/AuthContext';
import { getNotifications, markAsRead, getUnreadCount } from '../../services/notificationService';
import { Notification } from '../../types/types';

export const NotificationBell = () => {
    const [notifications, setNotifications] = useState<Notification[]>([]);
    const [unreadCount, setUnreadCount] = useState(0);
    const [anchorEl, setAnchorEl] = useState<null | HTMLElement>(null);
    const navigate = useNavigate();
    const { user } = useAuth();

    const fetchData = useCallback(async () => {
        if (!user) return;
        try {
            const [notifs, count] = await Promise.all([
                getNotifications(1, 5),
                getUnreadCount(),
            ]);
            setNotifications(notifs.data);
            setUnreadCount(count);
        } catch (err) {
            console.error('Failed to fetch notifications:', err);
        }
    }, [user]);

    useEffect(() => {
        fetchData();
        const interval = setInterval(fetchData, 30000);
        return () => clearInterval(interval);
    }, [fetchData]);

    const handleRead = async (id: string) => {
        try {
            await markAsRead(id);
            fetchData();
        } catch (err) {
            console.error('Failed to mark as read:', err);
        }
    };

    const handleAction = (notification: Notification) => {
        handleRead(notification.id);

        if (notification.data?.action === 'approve_guardian' && notification.data?.ward_id) {
            navigate(`/guardian/approve/${notification.data.ward_id}`, {
                state: {
                    ward: {
                        id: notification.data.ward_id,
                        name: notification.data.ward_name,
                        email: notification.data.ward_email,
                        date_of_birth: notification.data.ward_date_of_birth,
                        age: notification.data.ward_age,
                    },
                },
            });
        }
    };

    const getIconColor = (type: string): string => {
        switch (type) {
            case 'guardian_request': return 'warning.main';
            case 'guardian_approved': return 'success.main';
            case 'guardian_rejected': return 'error.main';
            default: return 'info.main';
        }
    };

    const open = Boolean(anchorEl);

    return (
        <>
            <IconButton
                onClick={(e) => setAnchorEl(e.currentTarget)}
                color="inherit"
                aria-label="notifications"
            >
                <Badge badgeContent={unreadCount} color="error">
                    <NotificationsIcon />
                </Badge>
            </IconButton>

            <Menu
                anchorEl={anchorEl}
                open={open}
                onClose={() => setAnchorEl(null)}
                slotProps={{
                    paper: {
                        sx: { width: 380, maxHeight: 480 },
                    },
                }}
                transformOrigin={{ horizontal: 'right', vertical: 'top' }}
                anchorOrigin={{ horizontal: 'right', vertical: 'bottom' }}
            >
                {notifications.length === 0 && (
                    <MenuItem disabled>
                        <ListItemText primary="Brak powiadomień" />
                    </MenuItem>
                )}

                {notifications.map((n) => (
                    <Box key={n.id}>
                        <MenuItem
                            onClick={() => handleAction(n)}
                            sx={{
                                bgcolor: n.read_at ? 'transparent' : 'action.hover',
                                borderLeft: 3,
                                borderColor: getIconColor(n.type),
                                py: 1.5,
                            }}
                        >
                            <ListItemIcon sx={{ minWidth: 36 }}>
                                <CircleIcon
                                    sx={{
                                        fontSize: 12,
                                        color: getIconColor(n.type),
                                    }}
                                />
                            </ListItemIcon>
                            <ListItemText
                                primary={
                                    <Typography
                                        component="span"
                                        variant="subtitle2"
                                        sx={{ fontWeight: n.read_at ? 400 : 600 }}
                                    >
                                        {n.title}
                                    </Typography>
                                }
                                secondary={
                                    <Box component="span" sx={{ display: 'block' }}>
                                        <Typography
                                            component="span"
                                            variant="body2"
                                            color="text.secondary"
                                            sx={{
                                                display: '-webkit-box',
                                                WebkitLineClamp: 2,
                                                WebkitBoxOrient: 'vertical',
                                                overflow: 'hidden',
                                            }}
                                        >
                                            {n.body}
                                        </Typography>
                                        <Typography
                                            component="span"
                                            variant="caption"
                                            color="text.secondary"
                                        >
                                            {new Date(n.created_at).toLocaleString('pl-PL')}
                                        </Typography>
                                    </Box>
                                }
                            />
                        </MenuItem>

                        {n.data?.action === 'approve_guardian' && !n.read_at && (
                            <Box sx={{ px: 2, pb: 1, bgcolor: 'action.hover' }}>
                                <Button
                                    size="small"
                                    variant="contained"
                                    color="success"
                                    fullWidth
                                    onClick={(e) => {
                                        e.stopPropagation();
                                        handleAction(n);
                                    }}
                                >
                                    Zatwierdź opiekę
                                </Button>
                            </Box>
                        )}

                        <Divider component="li" />
                    </Box>
                ))}

                {/* Link to full notifications page */}
                <MenuItem
                    onClick={() => {
                        setAnchorEl(null);
                        navigate('/notifications');
                    }}
                    sx={{ justifyContent: 'center' }}
                >
                    <ListItemIcon>
                        <ViewAllIcon fontSize="small" />
                    </ListItemIcon>
                    <ListItemText
                        primary={
                            <Typography
                                component="span"
                                variant="body2"
                                color="primary"
                                sx={{ fontWeight: 500 }}
                            >
                                Zobacz wszystkie powiadomienia
                            </Typography>
                        }
                    />
                </MenuItem>
            </Menu>
        </>
    );
};