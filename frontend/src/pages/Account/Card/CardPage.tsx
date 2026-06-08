import { useEffect, useState } from 'react';
import {
    Box,
    Typography,
    Card,
    CardContent,
    Grid,
    Chip,
    Skeleton,
    Alert,
    Pagination,
    Stack,
    Button,
    Dialog,
    DialogTitle,
    DialogContent,
    DialogActions,
    TableContainer,
    Table,
    TableHead,
    TableRow,
    TableCell,
    TableBody,
    Paper,
    IconButton,
    Divider,
} from '@mui/material';
import {
    CreditCard as CardIcon,
    Block as BlockIcon,
    LockOpen as UnblockIcon,
    Delete as DeleteIcon,
    Visibility as ViewIcon,
} from '@mui/icons-material';
import { getAllCards, blockCard, unblockCard, deleteCard } from '../../../services/accountService';
import type { Card as CardType, PaginationMeta } from '../../../types/types';
import { useToast } from '../../../context/ToastContext';
import { formatDate } from '../../../utils/formatDate';
import {FlikRequestButton} from "../../../components/Flik/FlikRequestButton";
import {formatCardNumber} from "../../../utils/formatCard";
import {getCardStatusColor, getCardStatusLabel, getCardTypeLabel} from "../../../utils/status/statusCard";

export const CardPage = () => {
    const [cards, setCards] = useState<CardType[]>([]);
    const [meta, setMeta] = useState<PaginationMeta | null>(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);
    const [page, setPage] = useState(1);
    const { showSuccess, showError } = useToast();

    // Delete confirmation
    const [deleteCardId, setDeleteCardId] = useState<string | null>(null);

    // Card details
    const [selectedCard, setSelectedCard] = useState<CardType | null>(null);
    const [openDetailsDialog, setOpenDetailsDialog] = useState(false);

    const [openFlikDialog, setOpenFlikDialog] = useState(false);

    const loadCards = async (pageNumber: number = 1) => {
        setLoading(true);
        setError(null);
        try {
            const response = await getAllCards(pageNumber);
            setCards(response.data);
            setMeta(response.meta);
        } catch (err: any) {
            setError(err.message || 'Błąd pobierania kart');
            showError(err.message || 'Błąd pobierania kart');
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        loadCards(page);
    }, [page]);

    const handlePageChange = (_: React.ChangeEvent<unknown>, value: number) => {
        setPage(value);
    };

    const handleBlockCard = async (cardId: string) => {
        try {
            await blockCard(cardId);
            await loadCards(page);
            showSuccess('Karta została zablokowana!');
        } catch (err: any) {
            const errorMsg = err.message || 'Błąd blokowania karty';
            setError(errorMsg);
            showError(errorMsg);
        }
    };


    const handleUnblockCard = async (cardId: string) => {
        try {
            await unblockCard(cardId);
            await loadCards(page);
            showSuccess('Karta została odblokowana!');
        } catch (err: any) {
            const errorMsg = err.message || 'Błąd odblokowywania karty';
            setError(errorMsg);
            showError(errorMsg);
        }
    };

    const handleDeleteCard = async () => {
        if (!deleteCardId) return;
        try {
            await deleteCard(deleteCardId);
            setDeleteCardId(null);
            await loadCards(page);
            showSuccess('Karta została usunięta!');
        } catch (err: any) {
            const errorMsg = err.message || 'Błąd usuwania karty';
            setError(errorMsg);
            showError(errorMsg);
        }
    };

    const handleViewDetails = (card: CardType) => {
        setSelectedCard(card);
        setOpenDetailsDialog(true);
    };

    return (
        <Box>
            <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', mb: 4 }}>
                <Typography variant="h4">Moje karty płatnicze</Typography>
                <Button variant="contained" onClick={() => setOpenFlikDialog(true)}>Wygeneruj kod FLIK</Button>
            </Box>

            {error && (
                <Alert severity="error" sx={{ mb: 3 }} onClose={() => setError(null)}>
                    {error}
                </Alert>
            )}

            {loading ? (
                <Grid container spacing={3}>
                    {[1, 2, 3].map((i) => (
                        <Grid size={{ xs: 12 }} key={i}>
                            <Skeleton variant="rounded" height={120} />
                        </Grid>
                    ))}
                </Grid>
            ) : cards.length === 0 ? (
                <Card sx={{ textAlign: 'center', py: 8 }}>
                    <CardContent>
                        <CardIcon sx={{ fontSize: 64, color: 'text.secondary', mb: 2 }} />
                        <Typography variant="h6" color="text.secondary">
                            Brak kart płatniczych
                        </Typography>
                        <Typography variant="body2" color="text.secondary" sx={{ mb: 3 }}>
                            Utwórz swoją pierwszą kartę płatniczą na stronie kont
                        </Typography>
                    </CardContent>
                </Card>
            ) : (
                <>
                    <TableContainer component={Paper} variant="outlined">
                        <Table>
                            <TableHead>
                                <TableRow sx={{ backgroundColor: 'action.hover' }}>
                                    <TableCell>Karta</TableCell>
                                    <TableCell>Network</TableCell>
                                    <TableCell>Typ</TableCell>
                                    <TableCell>Ważność</TableCell>
                                    <TableCell>Status</TableCell>
                                    <TableCell>Data utworzenia</TableCell>
                                    <TableCell align="right">Akcje</TableCell>
                                </TableRow>
                            </TableHead>
                            <TableBody>
                                {cards.map((card) => (
                                    <TableRow key={card.id} hover>
                                        <TableCell>
                                            <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
                                                <CardIcon color="action" />
                                                <Box>
                                                    <Typography variant="body1">
                                                        •••• {card.card_last_four}
                                                    </Typography>
                                                    <Typography variant="caption" color="text.secondary">
                                                        {formatCardNumber(card.card_number)}
                                                    </Typography>
                                                </Box>
                                            </Box>
                                        </TableCell>
                                        <TableCell>
                                            <Chip
                                                label={card.network.toUpperCase()}
                                                size="small"
                                                variant="outlined"
                                            />
                                        </TableCell>
                                        <TableCell>
                                            <Typography variant="body2">
                                                {getCardTypeLabel(card.type)}
                                            </Typography>
                                        </TableCell>
                                        <TableCell>
                                            <Typography variant="body2">
                                                {String(card.exp_month).padStart(2, '0')}/{card.exp_year}
                                            </Typography>
                                        </TableCell>
                                        <TableCell>
                                            <Chip
                                                label={getCardStatusLabel(card.status)}
                                                color={getCardStatusColor(card.status)}
                                                size="small"
                                            />
                                        </TableCell>
                                        <TableCell>
                                            <Typography variant="body2">
                                                {formatDate(card.created_at)}
                                            </Typography>
                                        </TableCell>
                                        <TableCell align="right">
                                            <Stack direction="row" spacing={0.5} sx={{ justifyContent: 'flex-end' }}>
                                                <IconButton
                                                    color="primary"
                                                    size="small"
                                                    onClick={() => handleViewDetails(card)}
                                                    title="Szczegóły"
                                                >
                                                    <ViewIcon />
                                                </IconButton>
                                                {card.status === 'active' && (
                                                    <IconButton
                                                        color="error"
                                                        size="small"
                                                        onClick={() => handleBlockCard(card.id)}
                                                        title="Zablokuj"
                                                    >
                                                        <BlockIcon />
                                                    </IconButton>
                                                )}
                                                {card.status === 'blocked' && (
                                                    <IconButton
                                                        color="success"
                                                        size="small"
                                                        onClick={() => handleUnblockCard(card.id)}
                                                        title="Odblokuj"
                                                    >
                                                        <UnblockIcon />
                                                    </IconButton>
                                                )}
                                                {card.status !== 'deleted' && (
                                                    <IconButton
                                                        color="default"
                                                        size="small"
                                                        onClick={() => setDeleteCardId(card.id)}
                                                        title="Usuń"
                                                    >
                                                        <DeleteIcon />
                                                    </IconButton>
                                                )}
                                            </Stack>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </TableContainer>

                    {meta && meta.last_page > 1 && (
                        <Stack spacing={2} sx={{ mt: 4, alignItems: 'center' }}>
                            <Pagination
                                count={meta.last_page}
                                page={meta.current_page}
                                onChange={handlePageChange}
                                color="primary"
                                showFirstButton
                                showLastButton
                            />
                            <Typography variant="body2" color="text.secondary">
                                Wyświetlono {meta.from ?? 0}-{meta.to ?? 0} z {meta.total} kart
                            </Typography>
                        </Stack>
                    )}
                </>
            )}

            {/* Card Details Dialog */}
            <Dialog open={openDetailsDialog} onClose={() => setOpenDetailsDialog(false)} maxWidth="sm" fullWidth>
                <DialogTitle>Szczegóły karty</DialogTitle>
                <DialogContent>
                    {selectedCard && (
                        <Stack spacing={2} sx={{ mt: 2 }}>
                            <Box>
                                <Typography variant="caption" color="text.secondary">
                                    Numer karty
                                </Typography>
                                <Typography variant="body1" sx={{ fontFamily: 'monospace', fontSize: '1.1rem' }}>
                                    {formatCardNumber(selectedCard.card_number)}
                                </Typography>
                            </Box>

                            <Divider />

                            <Box>
                                <Typography variant="caption" color="text.secondary">
                                    Network
                                </Typography>
                                <Typography variant="body1">{selectedCard.network.toUpperCase()}</Typography>
                            </Box>

                            <Box>
                                <Typography variant="caption" color="text.secondary">
                                    Typ karty
                                </Typography>
                                <Typography variant="body1">{getCardTypeLabel(selectedCard.type)}</Typography>
                            </Box>

                            <Box sx={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 2 }}>
                                <Box>
                                    <Typography variant="caption" color="text.secondary">
                                        Ważność
                                    </Typography>
                                    <Typography variant="body1">
                                        {String(selectedCard.exp_month).padStart(2, '0')}/{selectedCard.exp_year}
                                    </Typography>
                                </Box>
                                <Box>
                                    <Typography variant="caption" color="text.secondary">
                                        CVV
                                    </Typography>
                                    <Typography variant="body1">••• (ukryte)</Typography>
                                </Box>
                            </Box>

                            <Divider />

                            <Box>
                                <Typography variant="caption" color="text.secondary">
                                    Status
                                </Typography>
                                <Box sx={{ mt: 0.5 }}>
                                    <Chip
                                        label={getCardStatusLabel(selectedCard.status)}
                                        color={getCardStatusColor(selectedCard.status)}
                                        size="small"
                                    />
                                </Box>
                            </Box>

                            <Box sx={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 2 }}>
                                <Box>
                                    <Typography variant="caption" color="text.secondary">
                                        Data utworzenia
                                    </Typography>
                                    <Typography variant="body2">{formatDate(selectedCard.created_at)}</Typography>
                                </Box>
                                <Box>
                                    <Typography variant="caption" color="text.secondary">
                                        Ostatnia zmiana
                                    </Typography>
                                    <Typography variant="body2">{formatDate(selectedCard.updated_at)}</Typography>
                                </Box>
                            </Box>
                        </Stack>
                    )}
                </DialogContent>
                <DialogActions>
                    <Button onClick={() => setOpenDetailsDialog(false)}>Zamknij</Button>
                </DialogActions>
            </Dialog>

            {/* Delete Confirmation Dialog */}
            <Dialog open={!!deleteCardId} onClose={() => setDeleteCardId(null)}>
                <DialogTitle>Zamknąć kartę?</DialogTitle>
                <DialogContent>
                    <Typography>
                        Ta operacja jest nieodwracalna. Karta zostanie na trwale zamknięta.
                    </Typography>
                </DialogContent>
                <DialogActions>
                    <Button onClick={() => setDeleteCardId(null)}>Anuluj</Button>
                    <Button onClick={handleDeleteCard} color="error" variant="contained">
                        Zamknij kartę
                    </Button>
                </DialogActions>
            </Dialog>

            <FlikRequestButton
                open={openFlikDialog}
                onClose={() => setOpenFlikDialog(false)}
            />
        </Box>
    );
};

