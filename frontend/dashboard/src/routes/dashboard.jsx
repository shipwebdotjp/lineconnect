import React, { useState, useEffect } from "react";
import { Outlet, Link as ReactVisit } from "react-router-dom";
import { Download, RefreshCw, Search, User } from "lucide-react"


import { Button } from "@/components/ui/button"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { Input } from "@/components/ui/input"
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table"
import { Avatar, AvatarImage, AvatarFallback } from "@/components/ui/avatar"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";

const __ = wp.i18n.__;
export default function Dashboard() {
    const [data, setData] = useState([]);
    const [filteredData, setFilteredData] = useState([]);
    const [searchTerm, setSearchTerm] = useState("");
    const [isLoading, setIsLoading] = useState(false);
    const [error, setError] = useState(null);
    const [year, setYear] = useState(new Date().getFullYear());
    const [month, setMonth] = useState(new Date().getMonth() + 1);


    const currentDate = new Date();
    const currentYear = currentDate.getFullYear();
    const years = Array.from({ length: 5 }, (_, i) => currentYear - 2 + i);
    const months = Array.from({ length: 12 }, (_, i) => i + 1);

    const fetchData = () => {
        setIsLoading(true);
        setError(null);

        jQuery.ajax({
            url: window.lineConnectConfig.ajax_url,
            type: 'POST',
            data: {
                action: 'lc_ajax_get_dashboard',
                nonce: window.lineConnectConfig.ajax_nonce,
                ym: `${year}-${String(month).padStart(2, '0')}` // year and month in YYYYMM format
            },
            dataType: 'json'
        }).done(function (data) {
            // Transform the JSON data into array format for display
            const formattedData = Object.keys(data).map(key => {
                const item = data[key];
                return {
                    id: item.premiumId || item.basicId,
                    channel_prefix: key,
                    name: item.name, // Using the key as channel name for now
                    profile_url: 'https://line.me/R/ti/p/' + encodeURIComponent(item.premiumId || item.basicId),
                    icon_url: item.pictureUrl,
                    followers: parseInt(item.followers) || 0,
                    targetedReaches: parseInt(item.targetedReaches) || 0,
                    blocks: parseInt(item.blocks) || 0,
                    recognized: parseInt(item.recognized) || 0,
                    linked: parseInt(item.linked) || 0,
                    billableMessages: (
                        parseInt(item.broadcast || 0) +
                        parseInt(item.targeting || 0) +
                        parseInt(item.apiPush || 0) +
                        parseInt(item.apiMulticast || 0) +
                        parseInt(item.apiNarrowcast || 0) +
                        parseInt(item.apiBroadcast || 0)
                    ) || 0
                };
            });

            setData(formattedData);
            setFilteredData(formattedData);
        }).fail(function (xhr, status, error) {
            console.error('Error fetching data:', error);
            setError(__('Failed to fetch data. Please try again later.', 'lineconnect'));
        }).always(function () {
            setIsLoading(false);
        });
    };

    useEffect(() => {
        fetchData();
    }, [year, month]);

    const handleSearch = (e) => {
        const term = e.target.value;
        setSearchTerm(term);

        if (term === "") {
            setFilteredData(data);
            return;
        }

        const filtered = data.filter((item) =>
            item.name.toLowerCase().includes(term.toLowerCase())
        );
        setFilteredData(filtered);
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
    const handleRefresh = () => {
        fetchData();
    };

    const formatNumber = (num) => {
        return new Intl.NumberFormat(navigator.language).format(num);
    };

    return (
        <div className="flex min-h-screen w-full flex-col bg-gray-100">
            <div className="flex flex-col">
                <header className="flex h-12 items-center gap-4 px-6">
                    <h2 className="text-lg font-semibold">{__('Channel Stats', 'lineconnect')}</h2>
                </header>
                <main className="flex flex-1 flex-col gap-4 p-4 md:gap-8 md:p-8">
                    <div className="flex items-center justify-between">
                        <div className="relative flex-1 md:max-w-sm">
                            <Search className="absolute right-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                            <Input
                                type="search"
                                placeholder={__('Search by channel name', 'lineconnect')}
                                className="pl-8"
                                value={searchTerm}
                                onChange={handleSearch}
                            />
                        </div>

                        <div className="flex items-center gap-2">
                            <Button variant="outline" size="lg" onClick={handlePrevMonth}>
                                &larr; {__('Previous Month', 'lineconnect')}
                            </Button>
                            <Select
                                value={year}
                                onValueChange={(value) => setYear(value)}
                            >
                                <SelectTrigger className="w-[100px]">
                                    <SelectValue placeholder={year} />
                                </SelectTrigger>
                                <SelectContent>
                                    {years.map(y => (
                                        <SelectItem key={y} value={y}>{y}</SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            <Select
                                value={month}
                                onValueChange={(value) => setMonth(value)}
                            >
                                <SelectTrigger className="w-[80px]">
                                    <SelectValue placeholder={month} />
                                </SelectTrigger>
                                <SelectContent>
                                    {months.map((m, i) => (
                                        <SelectItem key={m} value={m}> {new Date(0, i).toLocaleString(navigator.language, { month: 'long' })}</SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            <Button variant="outline" size="lg" onClick={handleNextMonth}>
                                {__('Next Month', 'lineconnect')} &rarr;
                            </Button>
                            <Button variant="outline" size="icon" onClick={handleRefresh} disabled={isLoading}>
                                <RefreshCw className={`h-4 w-4 ${isLoading ? "animate-spin" : ""}`} />
                                <span className="sr-only">{__('Refresh', 'lineconnect')}</span>
                            </Button>
                        </div>
                    </div>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between">
                            <div>
                                <CardTitle>{__('Channel Stats', 'lineconnect')}: {new Date(year + '-' + month.toString().padStart(2, '0') + '-01').toLocaleDateString(navigator.language, { year: "numeric", month: "long" })}</CardTitle>
                            </div>
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
                                            <TableHead className="w-[200px]">{__('Channel Name', 'lineconnect')}</TableHead>
                                            <TableHead className="text-right">{__('Friends added', 'lineconnect')}</TableHead>
                                            <TableHead className="text-right">{__('Target reach', 'lineconnect')}</TableHead>
                                            <TableHead className="text-right">{__('Blocks', 'lineconnect')}</TableHead>
                                            <TableHead className="text-right">{__('Recognized count', 'lineconnect')}</TableHead>
                                            <TableHead className="text-right">{__('Linked', 'lineconnect')}</TableHead>
                                            <TableHead className="text-right">{__('Paid Messages', 'lineconnect')}</TableHead>
                                            <TableHead className="text-right">{__('Profile', 'lineconnect')}</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {isLoading ? (
                                            <TableRow>
                                                <TableCell colSpan={8} className="h-24 text-center">
                                                    <div className="flex justify-center items-center">
                                                        <RefreshCw className="h-6 w-6 animate-spin mr-2" />
                                                        {__('Loading...', 'lineconnect')}
                                                    </div>
                                                </TableCell>
                                            </TableRow>
                                        ) : filteredData.length > 0 ? (
                                            filteredData.map((item) => (
                                                <TableRow key={item.id}>
                                                    <TableCell className="font-medium">
                                                        <div className="flex items-center gap-2">
                                                            <Avatar className="h-8 w-8">
                                                                <AvatarImage src={item.icon_url} alt={item.name} />
                                                                <AvatarFallback>
                                                                    <User className="h-4 w-4" />
                                                                </AvatarFallback>
                                                            </Avatar>
                                                            <Link to={'/' + 'daily?channel=' + item.channel_prefix + '&name=' + encodeURIComponent(item.name) + '&ym=' + year + '-' + String(month).padStart(2, '0')}>
                                                                {item.name}
                                                            </Link>
                                                        </div>
                                                    </TableCell>
                                                    <TableCell className="text-right">{formatNumber(item.followers)}</TableCell>
                                                    <TableCell className="text-right">{formatNumber(item.targetedReaches)}</TableCell>
                                                    <TableCell className="text-right">{formatNumber(item.blocks)}</TableCell>
                                                    <TableCell className="text-right">{formatNumber(item.recognized)}</TableCell>
                                                    <TableCell className="text-right">{formatNumber(item.linked)}</TableCell>
                                                    <TableCell className="text-right">{formatNumber(item.billableMessages)}</TableCell>
                                                    <TableCell className="text-right"><a href={item.profile_url}>{__('Profile', 'lineconnect')}</a></TableCell>
                                                </TableRow>
                                            ))
                                        ) : (
                                            <TableRow>
                                                <TableCell colSpan={6} className="h-24 text-center">
                                                    {__('No data found', 'lineconnect')}
                                                </TableCell>
                                            </TableRow>
                                        )}
                                    </TableBody>
                                </Table>
                            </div>
                        </CardContent>
                    </Card>
                    <Outlet />
                </main>
            </div>
        </div>
    );
}