curpath=$(dirname $(readlink -f $0))

basepath=$(dirname $curpath)

dumpdir=$basepath"/dump"
symbol_gendir=$basepath"/symbol_gen"

day=`date "+%Y-%m-%d-%H-%M-%S"`
zip_dir=$1
target_dir=$2
package=$3
version=$4

tmp_dir="/disk1/dump/$package/$version/$day"
tmp_dir="$dumpdir/$package/$version/$day"

symbol_dir="/disk1/dump/symbols"
symbol_dir="$dumpdir/symbols"

params="/disk1/dump/params.php"
params="$dumpdir/params.php"

imdata="/disk1/dump/import_data.php"
imdata="$dumpdir/import_data.php"

if [ ! -d $target_dir ]
then
        mkdir -p $target_dir
fi

if [ ! -d $tmp_dir ]
then
        mkdir -p $tmp_dir
fi

cd $tmp_dir
for f in `ls $zip_dir/*.zip | sed 's/ //g'`
do 
	name=`basename $f | sed 's/\"//g' | sed 's///g' | sed 's/.zip$/.dmp/g'`; 
	realname=`echo $f | sed 's// /g'`
	zcat "$realname" > $name;
done

for f in `ls *.dmp`
do 
	name=`basename $f | sed 's/.dmp$/.stack/g'`; 
	#/usr/local/bin/minidump_stackwalk $f $symbol_dir 2>/dev/null > $name;
	minidump_stackwalk $f $symbol_dir 2>/dev/null > $name;
done

for f in `ls *.stack`
do
	name=`basename $f | sed 's/.stack$/.stack_brief/g'`; 
	grep '^[ 0-9][0-9][ 0-9]' $f | awk 'BEGIN{ln=0}{if($1==ln){print $0};ln++}' > $name ;
done

for f in `ls *.stack_brief`
do 
	name=`basename $f | sed 's/.stack_brief$/.stack_code/g'`; 
	grep '!' $f | sed 's/^[ 0-9][0-9][ 0-9]//' | sed '2,$ s/.....]$//g' > $name ;
done

#for f in `ls *stack_code`
#do 
#	md5sum $f | awk '{print $1}'; 
#done | sort | uniq -c | sort -nr | sed 's/^ \{1,\}//g' > $target_dir/count

ret="$tmp_dir/ret"

echo -n ''> $ret;
for f in `ls *stack_code`
do 
	md5=`md5sum $f`;
	name=`echo $md5 | awk '{print $1}'`; 
	[ ! -f "$target_dir/$name" ] && cp $f $target_dir/$name;
	md52file=`echo $md5 | awk -F. '{print $1}' `
	log_file=`echo $md52file | awk '{print $1}'`
	uuid=`echo $md52file | awk '{print $2}'`
	info="0,0,0,0,0"
	if [ -f $zip_dir/params_$uuid ]
	then
		info=`cat $zip_dir/params_$uuid | sed 's/\"/\\\"/g' | sed "s/'//g" | xargs php $params`
	fi
	echo "$version,$package,$info,$log_file,$uuid" >> $ret
done

sed -i 's/\"//g' $ret
php $imdata $ret
cd /disk1/dump/
cd $dumpdir/
echo "rm -rf $tmp_dir"
