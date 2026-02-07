export const mockDataService = {
    getNews: () => {
        // بنجهز الداتا هنا كأنها جاية من الـ API بالظبط
        return Promise.resolve([
            {
                id: 1,
                title: "اجتماع مجلس الجامعة لمناقشة الخطة الاستراتيجية",
                date: "2024-05-15",
                summary: "ناقش مجلس الجامعة الخطط المستقبلية والمبادرات البحثية الجديدة للعام الأكاديمي القادم.",
                content: "التفاصيل الكاملة للخبر هنا... ده الجزء اللي هيظهر لما نفتح صفحة الخبر.",
                image: "https://i.postimg.cc/cJHfF5tb/photo-2022-10-05-11-22-37.jpg",
                category: "أكاديمي",
                social_meta: {
                    fb_title: "BATU: خطة استراتيجية جديدة",
                    fb_image: "https://i.postimg.cc/cJHfF5tb/photo-2022-10-05-11-22-37.jpg"
                }
            },
            {
                id: 2,
                title: "استقبال الطلاب الجدد للعام الدراسي 2024",
                date: "2024-05-10",
                summary: "جامعة برج العرب التكنولوجية ترحب بكل الطلاب الجدد. تبدأ جلسات التعريف الأسبوع القادم.",
                content: "أهلاً بكم في بياتيو... جدول الاستقبال هيكون كالتالي...",
                image: "https://i.postimg.cc/cJHfF5tb/photo-2022-10-05-11-22-37.jpg",
                category: "شؤون طلاب",
                social_meta: {
                    fb_title: "أهلاً بكل الطلاب الجدد في BATU",
                    fb_image: "https://i.postimg.cc/cJHfF5tb/photo-2022-10-05-11-22-37.jpg"
                }
            },
            {
                id: 3,
                title: "شراكة دولية مع كبرى شركات التكنولوجيا",
                date: "2024-05-05",
                summary: "توقيع مذكرات تفاهم مع شركات عالمية لتوفير فرص تدريب لطلاب الجامعة.",
                content: "باتيو بتفتح آفاق جديدة للطلاب للتدريب في شركات عالمية...",
                image: "https://i.postimg.cc/cJHfF5tb/photo-2022-10-05-11-22-37.jpg",
                category: "شراكات",
                social_meta: {
                    fb_title: "فرص تدريب عالمية لطلاب BATU",
                    fb_image: "https://i.postimg.cc/cJHfF5tb/photo-2022-10-05-11-22-37.jpg"
                }
            }
        ]);
    },

    getAdministrations: () => {
        return Promise.resolve([
            {
                id: 1,
                name: "Faculty of Industry & Energy",
                message: "Leading the way in technological innovation and industrial excellence.",
                directorImage: "https://i.postimg.cc/qRYfNY2z/DR-M-G.png", // Using president as placeholder
                directorName: "Prof. Dr. Ahmed Ali"
            },
            {
                id: 2,
                name: "Graduate Studies & Research",
                message: "Fostering an environment of high-impact research and academic growth.",
                directorImage: "https://i.postimg.cc/qRYfNY2z/DR-M-G.png",
                directorName: "Prof. Dr. Sarah Hassan"
            },
            {
                id: 3,
                name: "Student Affairs",
                message: "Supporting our students throughout their academic journey and beyond.",
                directorImage: "https://i.postimg.cc/qRYfNY2z/DR-M-G.png",
                directorName: "Dr. Khaled Fawzy"
            },
            {
                id: 4,
                name: "Financial & Administrative Affairs",
                message: "Ensuring operational excellence and transparency in university management.",
                directorImage: "https://i.postimg.cc/qRYfNY2z/DR-M-G.png",
                directorName: "Mr. Mahmoud Zaki"
            }
        ]);
    }
};
