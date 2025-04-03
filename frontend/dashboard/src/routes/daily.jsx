import * as React from 'react';
import { useState, useEffect } from 'react';
import { useSearchParams, Link } from 'react-router-dom';
import { RefreshCw, ArrowLeft } from 'lucide-react';

import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";

const __ = wp.i18n.__;

const Daily = () => {
    const [searchParams] = useSearchParams();
    const [isLoading, setIsLoading] = useState(true);
    const [data, setData] = useState([]);
    // const [yearMonth, setYearMonth] = useState(searchParams.get('ym') || new Date().toISOString().slice(0, 7));
    const [year, setYear] = useState(new Date().getFullYear());
    const [month, setMonth] = useState(new Date().getMonth() + 1);
    const [channelPrefix, setChannelPrefix] = useState(searchParams.get('channel') || '');
    const [channelName, setChannelName] = useState(searchParams.get('name') || '');
    const [error, setError] = useState(null);

    const currentDate = new Date();
    const currentYear = currentDate.getFullYear();
    const years = Array.from({ length: 5 }, (_, i) => currentYear - 2 + i);
    const months = Array.from({ length: 12 }, (_, i) => i + 1);

    useEffect(() => {
        fetchData();
    }, [year, month, channelPrefix]);

    const fetchData = async () => {
        setIsLoading(true);
        setError(null);

        jQuery.ajax({
            url: window.lineConnectConfig.ajax_url,
            type: 'POST',
            data: {
                action: 'lc_ajax_get_dashboard',
                period: 'daily',
                ym: year + '-' + month.toString().padStart(2, '0'),
                channel_prefix: channelPrefix,
                nonce: window.lineConnectConfig.ajax_nonce
            },
            dataType: 'json'
        }).done(function (result) {
            setData(result);
        }).fail(function (xhr, status, error) {
            console.error('Error fetching data:', error);
            setError(__('Failed to fetch data. Please try again later.', 'lineconnect'));
        }).always(function () {
            setIsLoading(false);
        });
    };

    const handlePrevMonth = () => {
        const date = new Date(year + '-' + month.toString().padStart(2, '0') + '-01');
        date.setMonth(date.getMonth() - 1);
        setYear(date.getFullYear());
        setMonth(date.getMonth() + 1);
    };

    const handleNextMonth = () => {
        const date = new Date(year + '-' + month.toString().padStart(2, '0') + '-01');
        date.setMonth(date.getMonth() + 1);
        setYear(date.getFullYear());
        setMonth(date.getMonth() + 1);
    };

    const formatDate = (dateString) => {
        const date = new Date(dateString);
        return date.toLocaleDateString(navigator.language);
    };

    const formatNumber = (num) => {
        return new Intl.NumberFormat(navigator.language).format(num || 0);
    };

    const calculatePaidMessages = (item) => {
        return (
            (parseInt(item.broadcast || 0) +
                parseInt(item.targeting || 0) +
                parseInt(item.apiBroadcast || 0) +
                parseInt(item.apiPush || 0) +
                parseInt(item.apiMulticast || 0) +
                parseInt(item.apiNarrowcast || 0))
        );
    };

    return (
        <div className="flex min-h-screen w-full flex-col bg-gray-100">
            <div className="flex flex-col">
                <header className="flex h-12 items-center gap-4 border-b px-6">
                    <h2 className="text-lg font-semibold">
                        {channelName} - {__('Daily Statistics', 'lineconnect')}
                    </h2>
                </header>
                <main className="flex flex-1 flex-col gap-4 p-4 md:gap-8 md:p-8">
                    <div className="flex items-center justify-between">
                        <Button variant="outline" size="lg" asChild>
                            <Link to="/">
                                <ArrowLeft className="mr-2 h-4 w-4" />
                                {__('Back to Overview', 'lineconnect')}
                            </Link>
                        </Button>
                        <div className="flex items-center gap-2">
                            <Button variant="outline" size="lg" onClick={handlePrevMonth}>
                                &larr; {__('Previous Month', 'lineconnect')}
                            </Button>
                            <div className="text-sm font-medium">
                                <div className="flex items-center gap-2">
                                    <Select value={year} onValueChange={(value) => setYear(value)}>
                                        <SelectTrigger className="w-[100px]">
                                            <SelectValue placeholder={year} />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {years.map(y => (
                                                <SelectItem key={y} value={y}>{y}</SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    <Select value={month} onValueChange={(value) => setMonth(value)}>
                                        <SelectTrigger className="w-[80px]">
                                            <SelectValue placeholder={month} />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {months.map((m, i) => (
                                                <SelectItem key={m} value={m}>{new Date(0, i).toLocaleString(navigator.language, { month: 'long' })}</SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </div>
                            </div>
                            <Button variant="outline" size="lg" onClick={handleNextMonth}>
                                {__('Next Month', 'lineconnect')} &rarr;
                            </Button>
                            <Button variant="outline" size="icon" onClick={fetchData} disabled={isLoading}>
                                <RefreshCw className={`h-4 w-4 ${isLoading ? "animate-spin" : ""}`} />
                                <span className="sr-only">{__('Refresh', 'lineconnect')}</span>
                            </Button>
                        </div>
                    </div>

                    <Card>
                        <CardHeader>
                            <CardTitle>{__('Daily Channel Statistics', 'lineconnect')}</CardTitle>
                        </CardHeader>
                        <CardContent>
                            {error && (
                                <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                                    {error}
                                </div>
                            )}
                            <div className="overflow-auto">
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead>{__('Date', 'lineconnect')}</TableHead>
                                            <TableHead className="text-right">{__('Followers', 'lineconnect')}</TableHead>
                                            <TableHead className="text-right">{__('Target reach', 'lineconnect')}</TableHead>
                                            <TableHead className="text-right">{__('Blocks', 'lineconnect')}</TableHead>
                                            <TableHead className="text-right">{__('Linked', 'lineconnect')}</TableHead>
                                            <TableHead className="text-right">{__('New Follows', 'lineconnect')}</TableHead>
                                            <TableHead className="text-right">{__('Unfollows', 'lineconnect')}</TableHead>
                                            <TableHead className="text-right">{__('New Links', 'lineconnect')}</TableHead>
                                            <TableHead className="text-right">{__('Unlinks', 'lineconnect')}</TableHead>
                                            <TableHead className="text-right">{__('Paid Messages', 'lineconnect')}</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {isLoading ? (
                                            <TableRow>
                                                <TableCell colSpan={10} className="h-24 text-center">
                                                    <div className="flex justify-center items-center">
                                                        <RefreshCw className="h-6 w-6 animate-spin mr-2" />
                                                        {__('Loading...', 'lineconnect')}
                                                    </div>
                                                </TableCell>
                                            </TableRow>
                                        ) : data.length > 0 ? (
                                            data.map((item, index) => (
                                                <TableRow key={index}>
                                                    <TableCell className="font-medium">{formatDate(item.date)}</TableCell>
                                                    <TableCell className="text-right">{formatNumber(item.followers)}</TableCell>
                                                    <TableCell className="text-right">{formatNumber(item.targetedReaches)}</TableCell>
                                                    <TableCell className="text-right">{formatNumber(item.blocks)}</TableCell>
                                                    <TableCell className="text-right">{formatNumber(item.linked)}</TableCell>
                                                    <TableCell className="text-right">{formatNumber(item.follow)}</TableCell>
                                                    <TableCell className="text-right">{formatNumber(item.unfollow)}</TableCell>
                                                    <TableCell className="text-right">{formatNumber(item.link)}</TableCell>
                                                    <TableCell className="text-right">{formatNumber(item.unlink)}</TableCell>
                                                    <TableCell className="text-right">{formatNumber(calculatePaidMessages(item))}</TableCell>
                                                </TableRow>
                                            ))
                                        ) : (
                                            <TableRow>
                                                <TableCell colSpan={10} className="h-24 text-center">
                                                    {__('No data available for this period.', 'lineconnect')}
                                                </TableCell>
                                            </TableRow>
                                        )}
                                    </TableBody>
                                </Table>
                            </div>
                        </CardContent>
                    </Card>
                </main>
            </div >
        </div >
    );
};

export default Daily;
