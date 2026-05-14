import { useState, useEffect, useCallback } from 'react';
import { useNavigate } from 'react-router-dom';
import {
    Container,
    Typography,
    Box,
    Card,
    CardContent,
    Button,
    IconButton,
    Chip,
    Divider,
    List,
    ListItem,
    ListItemButton,
    ListItemText,
    Badge,
    Pagination,
    Skeleton,
    Alert,
    Tooltip,
} from '@mui/material';
import {
    NotificationsActive as UnreadIcon,
    DoneAll as MarkAllReadIcon,
    ArrowBack as BackIcon,
    Circle as CircleIcon,
    DeleteOutlineRounded as DeleteIcon,
} from '@mui/icons-material';
import { useAuth } from '../../context/AuthContext';
import { useToast } from '../../context/ToastContext';
import { getNotifications, markAsRead, markAllAsRead } from '../../services/notificationService';
import { Notification } from '../../types/types';
import { formatRelative } from '../../utils/formatDate';

export const NotificationsPage = () => {
    const navigate = useNavigate();
    const { showSuccess, showError } = useToast();

    const [notifications, setNotifications] = useState<Notification[]>([]);
    const [loading, setLoading] = useState(true);
    const [page, setPage] = useState(1);
    const [totalPages, setTotalPages] = useState(1);
    const perPage = 50;

    const fetchNotifications = useCallback(async () => {
        setLoading(true);
        try {
            const response = await getNotifications(page, perPage);
            setNotifications(response.data);
            setTotalPages(response.meta.last_page || 1);
        } catch (err: any) {
            showError(err.message || 'Błąd pobierania powiadomień');
        } finally {
            setLoading(false);
        }
    }, [page, perPage]);

    useEffect(() => {
        fetchNotifications();
    }, [fetchNotifications]);

    const handleMarkAsRead = async (id: string, e?: React.MouseEvent) => {
        e?.stopPropagation();
        try {
            await markAsRead(id);
            setNotifications(prev => prev.map(n =>
                n.id === id ? { ...n, read_at: new Date().toISOString() } : n
            ));
            showSuccess('Oznaczono jako przeczytane');
        } catch (err: any) {
            showError(err.message);
        }
    };

    const handleMarkAllAsRead = async () => {
        try {
            await markAllAsRead();
            setNotifications(prev => prev.map(n => ({ ...n, read_at: new Date().toISOString() })));
            showSuccess('Wszystkie oznaczone jako przeczytane');
        } catch (err: any) {
            showError(err.message);
        }
    };

    const handleNotificationClick = (notification: Notification) => {
        if (!notification.read_at) {
            handleMarkAsRead(notification.id);
        }

        // Nawigacja w zależności od typu
        switch (notification.data?.action) {
            case 'approve_guardian':
                if (notification.data.ward_id) {
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
                break;
            case 'view_account':
                navigate('/accounts');
                break;
            default:
                // Nic nie rób — tylko oznacz jako przeczytane
                break;
        }
    };

    const getTypeColor = (type: string) => {
        switch (type) {
            case 'guardian_request': return 'warning';
            case 'guardian_approved': return 'success';
            case 'guardian_rejected': return 'error';
            case 'guardian_request_sent': return 'info';
            default: return 'default';
        }
    };

    const getTypeLabel = (type: string) => {
        switch (type) {
            case 'guardian_request': return 'Prośba o opiekę';
            case 'guardian_approved': return 'Opiekun zatwierdzony';
            case 'guardian_rejected': return 'Opiekun odrzucony';
            case 'guardian_request_sent': return 'Prośba wysłana';
            default: return 'Powiadomienie';
        }
    };

    const unreadCount = notifications.filter(n => !n.read_at).length;

    return (
        <Container maxWidth="md" sx={{ py: 4 }}>
            {/* Header */}
            <Box sx={{ display: 'flex', alignItems: 'center', mb: 3, gap: 2 }}>
                <IconButton onClick={() => navigate(-1)}>
                    <BackIcon />
                </IconButton>
                <Typography variant="h5" sx={{ flexGrow: 1 }}>
                    Powiadomienia
                </Typography>
                {unreadCount > 0 && (
                    <Tooltip title="Oznacz wszystkie jako przeczytane">
                        <Button
                            startIcon={<MarkAllReadIcon />}
                            onClick={handleMarkAllAsRead}
                            size="small"
                        >
                            Oznacz wszystkie
                        </Button>
                    </Tooltip>
                )}
            </Box>

            {/* Unread badge */}
            {unreadCount > 0 && (
                <Alert
                    severity="info"
                    icon={<UnreadIcon />}
                    sx={{ mb: 2 }}
                >
                    Masz {unreadCount} nieprzeczytanych powiadomień
                </Alert>
            )}

            {/* Lista powiadomień */}
            <Card>
                {loading ? (
                    <Box sx={{ p: 2 }}>
                        {[...Array(5)].map((_, i) => (
                            <Skeleton key={i} height={80} sx={{ mb: 1 }} />
                        ))}
                    </Box>
                ) : notifications.length === 0 ? (
                    <CardContent sx={{ textAlign: 'center', py: 6 }}>
                        <Typography color="text.secondary">
                            Brak powiadomień
                        </Typography>
                    </CardContent>
                ) : (
                    <List sx={{ p: 0 }}>
                        {notifications.map((notification, index) => (
                            <Box key={notification.id}>
                                <ListItem
                                    disablePadding
                                    secondaryAction={
                                        !notification.read_at && (
                                            <Tooltip title="Oznacz jako przeczytane">
                                                <IconButton
                                                    edge="end"
                                                    onClick={(e) => handleMarkAsRead(notification.id, e)}
                                                    size="small"
                                                >
                                                    <CircleIcon sx={{ fontSize: 12, color: 'primary.main' }} />
                                                </IconButton>
                                            </Tooltip>
                                        )
                                    }
                                >
                                    <ListItemButton
                                        onClick={() => handleNotificationClick(notification)}
                                        sx={{
                                            bgcolor: notification.read_at ? 'transparent' : 'action.hover',
                                            borderLeft: 4,
                                            borderColor: `${getTypeColor(notification.type)}.main`,
                                            py: 2,
                                            pr: 6, // miejsce na secondaryAction
                                        }}
                                    >
                                        <ListItemText
                                            primary={
                                                <Box sx={{ display: 'flex', alignItems: 'center', gap: 1, mb: 0.5 }}>
                                                    <Typography
                                                        component="span"
                                                        variant="subtitle2"
                                                        sx={{ fontWeight: notification.read_at ? 400 : 600 }}
                                                    >
                                                        {notification.title}
                                                    </Typography>
                                                    <Chip
                                                        label={getTypeLabel(notification.type)}
                                                        color={getTypeColor(notification.type) as any}
                                                        size="small"
                                                        variant="outlined"
                                                    />
                                                    {!notification.read_at && (
                                                        <Badge color="error" variant="dot" />
                                                    )}
                                                </Box>
                                            }
                                            secondary={
                                                <Box>
                                                    <Typography
                                                        variant="body2"
                                                        color="text.secondary"
                                                        sx={{ mb: 0.5 }}
                                                    >
                                                        {notification.body}
                                                    </Typography>
                                                    <Typography variant="caption" color="text.secondary">
                                                        {formatRelative(notification.created_at)}
                                                    </Typography>
                                                </Box>
                                            }
                                        />
                                    </ListItemButton>
                                </ListItem>
                                {index < notifications.length - 1 && <Divider component="li" />}
                            </Box>
                        ))}
                    </List>
                )}
            </Card>

            {/* Pagination */}
            {totalPages > 1 && (
                <Box sx={{ display: 'flex', justifyContent: 'center', mt: 3 }}>
                    <Pagination
                        count={totalPages}
                        page={page}
                        onChange={(_, value) => setPage(value)}
                        color="primary"
                    />
                </Box>
            )}
        </Container>
    );
};