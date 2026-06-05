import { useEffect, useState } from 'react';
import { Box, Typography, TableContainer, Paper, Table, TableHead, TableRow, TableCell, TableBody, Chip, Pagination, Stack, Button, Dialog } from '@mui/material';
import { getTransactions, getTransaction } from '../../services/accountService';
import type { Transaction, PaginationMeta } from '../../types/types';
import { formatDate } from '../../utils/formatDate';
import { useToast } from '../../context/ToastContext';
import { TransferDialog } from '../../components/Transfer/TransferDialog';
import {FlikRequestButton} from "../../components/Flik/FlikRequestButton";
import {FlikPayDialog} from "../../components/Flik/FlikPayDialog";
import {formatStatusTransaction, formatTypeTransaction} from "../../utils/formatStatusTransaction";

export const TransactionsPage = () => {
    const [transactions, setTransactions] = useState<Transaction[]>([]);
    const [meta, setMeta] = useState<PaginationMeta | null>(null);
    const [loading, setLoading] = useState(true);
    const [page, setPage] = useState(1);
    const [error, setError] = useState<string | null>(null);
    const [selectedTx, setSelectedTx] = useState<Transaction | null>(null);
    const [openTransfer, setOpenTransfer] = useState(false);
    const [openFlikPay, setOpenFlikPay] = useState(false);
    const { showError } = useToast();

    const load = async (pageNumber: number = 1) => {
        setLoading(true);
        try {
            const res = await getTransactions(pageNumber);
            setTransactions(res.data || []);
            setMeta(res.meta || null);
        } catch (err: any) {
            setError(err.message || 'Błąd pobierania transakcji');
            showError(err.message || 'Błąd pobierania transakcji');
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => { load(page); }, [page]);

    if (loading) {
        return <Box><Typography>Ładowanie...</Typography></Box>;
    }

    return (
        <Box>
            <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', mb: 2 }}>
                <Typography variant="h4">Transakcje</Typography>
                <Box sx={{ display: 'flex', gap: 8 }}>
                    <Button variant="outlined" onClick={() => setOpenTransfer(true)}>Nowy przelew</Button>
                    <Button variant="outlined" onClick={() => setOpenFlikPay(true)}>FLIK przelew</Button>
                    <Button variant="outlined">Eksport</Button>
                </Box>
            </Box>

            <TableContainer component={Paper}>
                <Table>
                    <TableHead>
                        <TableRow>
                            <TableCell>Data</TableCell>
                            <TableCell>Opis</TableCell>
                            <TableCell>Nadawca</TableCell>
                            <TableCell>Odbiorca</TableCell>
                            <TableCell>Kwota</TableCell>
                            <TableCell>Typ</TableCell>
                            <TableCell>Status</TableCell>
                            <TableCell>Akcje</TableCell>
                        </TableRow>
                    </TableHead>
                    <TableBody>
                        {transactions.length === 0 ? (
                            <TableRow>
                                <TableCell colSpan={8} align="center">
                                    Brak transakcji
                                </TableCell>
                            </TableRow>
                        ) : (
                            transactions.map(tx => (
                                    <TableRow key={tx.id} hover>
                                        <TableCell>{formatDate(tx.created_at)}</TableCell>
                                        <TableCell>{tx.description || tx.reference || '-'}</TableCell>
                                        <TableCell>{tx.from_account?.user ?? '-'}</TableCell>
                                        <TableCell>{tx.to_account?.user ?? '-'}</TableCell>
                                        <TableCell>{(tx.amount/100).toFixed(2)} PLN</TableCell>
                                        <TableCell>{formatTypeTransaction(tx.type)}</TableCell>
                                        <TableCell>
                                            <Chip
                                                label={formatStatusTransaction(tx.status)}
                                                color={tx.status === 'completed' ? 'success' : tx.status === 'failed' ? 'error' : 'warning'}
                                                size="small"
                                            />
                                        </TableCell>
                                        <TableCell>
                                            <Button size="small" onClick={async () => {
                                                try {
                                                    const details = await getTransaction(tx.id);
                                                    setSelectedTx(details);
                                                } catch (err: any) {
                                                    showError(err.message || 'Błąd');
                                                }
                                            }}>Szczegóły</Button>
                                        </TableCell>
                                    </TableRow>
                                )
                            )
                        )}
                    </TableBody>
                </Table>
            </TableContainer>

            {meta && meta.last_page > 1 && (
                <Stack spacing={2} sx={{ mt: 2, alignItems: 'center' }}>
                    <Pagination count={meta.last_page} page={meta.current_page} onChange={(_, v) => setPage(v)} />
                </Stack>
            )}

            <Dialog open={!!selectedTx} onClose={() => setSelectedTx(null)} maxWidth="sm" fullWidth>
                <Box sx={{ p: 2 }}>
                    <Typography variant="h6">Szczegóły transakcji</Typography>
                    {selectedTx && (
                        <Box>
                            <Typography>Status: {selectedTx.status}</Typography>
                            <Typography>Kwota: {(selectedTx.amount/100).toFixed(2)} PLN</Typography>
                            <Typography>Od: {selectedTx.from_account?.account_number ?? '-'} ({selectedTx.from_account?.user ?? '-'})</Typography>
                            <Typography>Do: {selectedTx.to_account?.account_number ?? '-'} ({selectedTx.to_account?.user ?? '-'})</Typography>
                            <Typography>Opis: {selectedTx.description ?? '-'}</Typography>
                        </Box>
                    )}
                </Box>
            </Dialog>
            <TransferDialog
                open={openTransfer}
                onClose={() => {
                    setOpenTransfer(false);
                }}
                callback={() => {
                    load();
                }}
            />
            <FlikPayDialog
                open={openFlikPay}
                onClose={() => {
                    setOpenFlikPay(false);
                }}
                callback={() => {
                    load();
                }}
            />
        </Box>
    );
};


